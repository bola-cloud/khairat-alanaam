<?php

namespace App\Http\Controllers\Api\Whatsapp;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\Category;
use App\Models\Admin\Product;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\DeliveryCharge;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;

class WhatsappStoreController extends Controller
{
    /**
     * Register a new user via WhatsApp Bot
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $phone = $request->phone;
        // Ensure email uniqueness by using phone-based dummy email
        $email = $phone . '@hispeed.om';

        // Check if phone number or dummy email is already taken
        if (User::where('Number', $phone)->orWhere('email', $email)->exists()) {
            return response()->json(['errors' => ['phone' => [__('The phone number or email has already been taken.')]]], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'Number' => $phone,
            'password' => Hash::make(\Illuminate\Support\Str::random(12)),
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('whatsapp_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login user via WhatsApp Bot
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('Number', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $token = $user->createToken('whatsapp_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Get all active categories
     */
    public function getCategories()
    {
        $categories = Category::where('Status', 1)->orderBy('order', 'asc')->get();
        return CategoryResource::collection($categories);
    }

    /**
     * Get products with optional filtering
     */
    public function getProducts(Request $request)
    {
        $query = Product::with(['category', 'brand', 'weights', 'sizes', 'additions'])
            ->available();

        if ($request->filled('category_id')) {
            $query->where('Category_Id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('en_Product_Name', 'like', "%{$search}%")
                  ->orWhere('fr_Product_Name', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('fr_Product_Name', 'asc')->paginate($request->get('per_page', 20));

        return ProductResource::collection($products);
    }

    /**
     * Get ALL products for Master Catalog (No availability filtering)
     * Requested for Facebook/WhatsApp Catalog synchronization
     */
    public function getCatalog(Request $request)
    {
        // Fetch EVERYTHING: Active, Inactive, Out of Stock, even Combos with 0 components
        // We use the same relationships as getProducts to ensure data consistency
        $query = Product::with(['category', 'brand', 'weights', 'sizes', 'additions']);

        if ($request->filled('category_id')) {
            $query->where('Category_Id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('en_Product_Name', 'like', "%{$search}%")
                  ->orWhere('fr_Product_Name', 'like', "%{$search}%");
            });
        }

        // Return a larger page size by default for catalogs (100)
        $products = $query->orderBy('id', 'desc')->paginate($request->get('per_page', 100));

        return ProductResource::collection($products);
    }

    /**
     * Get a single product detail by ID (same data as product details page)
     */
    public function getProductDetail($id)
    {
        $product = Product::with([
            'brand',
            'category',
            'colors',
            'sizes',
            'weights',
            'additions' => function ($query) {
                $query->where('status', 1);
            },
            'product_tags',
            'product_reviews',
            'product_reviews.user',
        ])->available()->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Build related products (same category + name keyword matching)
        $cat_id = $product->Category_Id;
        $nameSource = $product->en_Product_Name ?? $product->fr_Product_Name ?? '';
        $words = preg_split('/\s+/', strip_tags($nameSource));
        $keywords = array_slice(array_values(array_filter($words, function ($w) {
            return mb_strlen(trim($w)) > 2;
        })), 0, 3);

        $related = Product::with(['category', 'brand', 'weights', 'sizes'])
            ->where('Status', 1)
            ->available()
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($cat_id, $keywords) {
                if (!empty($cat_id)) {
                    $q->where('Category_Id', $cat_id);
                }
                if (!empty($keywords)) {
                    $q->orWhere(function ($q2) use ($keywords) {
                        foreach ($keywords as $kw) {
                            $kw = trim($kw);
                            if ($kw === '') continue;
                            $q2->orWhere('en_Product_Name', 'LIKE', "%{$kw}%")
                               ->orWhere('fr_Product_Name', 'LIKE', "%{$kw}%");
                        }
                    });
                }
            })
            ->latest()
            ->take(5)
            ->get();

        // Build full image list
        $images = array_values(array_filter([
            $product->resizeImage(),
            $product->Image2 ? asset('images/product/' . $product->Image2) : null,
            $product->Image3 ? asset('images/product/' . $product->Image3) : null,
            $product->Image4 ? asset('images/product/' . $product->Image4) : null,
            $product->Image5 ? asset('images/product/' . $product->Image5) : null,
        ]));

        // Calculate average rating
        $reviews = $product->product_reviews ?? collect();
        $avgRating = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : 0;

        return response()->json([
            'id' => $product->id,
            'en_Product_Name' => $product->en_Product_Name,
            'fr_Product_Name' => $product->fr_Product_Name,
            'en_Product_Slug' => $product->en_Product_Slug,
            'fr_Product_Slug' => $product->fr_Product_Slug,
            'Price' => $product->Price,
            'Discount_Price' => $product->Discount_Price,
            'Discount' => $product->Discount,
            'en_Description' => $product->en_Description,
            'fr_Description' => $product->fr_Description,
            'en_About' => $product->en_About,
            'fr_About' => $product->fr_About,
            'en_ShippingReturn' => $product->en_ShippingReturn,
            'fr_ShippingReturn' => $product->fr_ShippingReturn,
            'en_AdditionalInformation' => $product->en_AdditionalInformation,
            'fr_AdditionalInformation' => $product->fr_AdditionalInformation,
            'Quantity' => $product->virtual_stock,
            'in_stock' => $product->virtual_stock > 0,
            'images' => $images,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'en_Category_Name' => $product->category->en_Category_Name,
                'fr_Category_Name' => $product->category->fr_Category_Name,
            ] : null,
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'en_BrandName' => $product->brand->en_BrandName,
                'fr_BrandName' => $product->brand->fr_BrandName,
            ] : null,
            'weights' => $product->weights->map(function ($w) {
                return [
                    'id' => $w->id,
                    'weight' => $w->weight,
                    'price' => $w->price,
                ];
            }),
            'sizes' => $product->sizes->map(function ($s) {
                return [
                    'id' => $s->id,
                    'Size' => $s->Size,
                    'price' => $s->pivot->price ?? 0,
                    'weight' => $s->pivot->weight ?? 0,
                ];
            }),
            'additions' => $product->additions->map(function ($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name,
                    'name_ar' => $a->name_ar,
                    'price' => $a->price,
                    'icon' => $a->icon,
                ];
            }),
            'colors' => $product->colors->map(function ($c) {
                return [
                    'id' => $c->id,
                    'Name' => $c->Name,
                    'Code' => $c->Code,
                ];
            }),
            'reviews' => [
                'average_rating' => $avgRating,
                'total_count' => $reviews->count(),
                'items' => $reviews->take(10)->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'rating' => $r->rating,
                        'feedback' => $r->feedback,
                        'user_name' => $r->user->name ?? 'Guest',
                        'created_at' => $r->created_at?->toDateTimeString(),
                    ];
                }),
            ],
            'related_products' => ProductResource::collection($related),
        ]);
    }

    /**
     * Get hierarchy of shipping locations with delivery charges
     */
    public function getShippingLocations()
    {
        $countries = Country::with(['states.cities.areas'])->get()->map(function($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'name_ar' => $country->name_ar,
                'delivery_charge' => delivery_charge($country->name), // Fallback country charge
                'states' => $country->states->map(function($state) {
                    return [
                        'id' => $state->id,
                        'name' => $state->name_en,
                        'name_ar' => $state->name_ar,
                        'delivery_charge' => delivery_charge($state->id, 'state'), 
                        'cities' => $state->cities->map(function($city) {
                            return [
                                'id' => $city->id,
                                'name' => $city->name_en,
                                'name_ar' => $city->name_ar,
                                'delivery_charge' => delivery_charge($city->id, 'city'),
                                'areas' => $city->areas->map(function($area) {
                                    return [
                                        'id' => $area->id,
                                        'name' => $area->name_en,
                                        'name_ar' => $area->name_ar,
                                        'delivery_charge' => delivery_charge($area->id, 'area'),
                                    ];
                                })
                            ];
                        })
                    ];
                })
            ];
        });

        return response()->json($countries);
    }

    /**
     * Checkout order from WhatsApp Bot
     */
    public function checkout(\App\Http\Requests\StoreOrderRequest $request)
    {
        $validated = $request->validated();
        
        // Set locale based on bot request if provided
        if (isset($validated['language'])) {
            app()->setLocale($validated['language']);
        }

        $user = auth()->user();
        $user_id = $user->id;

        // Calculate Totals
        $subtotal = $this->calculateSubtotal($validated['cart_items']);

        // Enforce Minimum Order Amount
        $min_order_amount = floatval(allsetting('min_order_amount') ?: 3.990);
        if ($subtotal < $min_order_amount) {
            return response()->json([
                'message' => __('Minimum order amount is :amount OMR.', ['amount' => number_format($min_order_amount, 3)])
            ], 422);
        }
        
        $tax = tax_amount($subtotal, $validated['billing_country']);
        
        $area_id = $validated['billing_area_id'] ?? null;
        $city_id = $validated['billing_city'] ?? null;
        $state_id = $validated['billing_state'] ?? null;
        $country = $validated['billing_country'] ?? 'Oman';

        // Determine Shipping Charge
        if (isset($validated['shipping_charge'])) {
            $shipping_charge = floatval($validated['shipping_charge']);
        } else {
            // Sanitize IDs from WhatsApp Bot (handling cases where they send string "null" or "undefined")
            $rawAreaId = $validated['billing_area_id'] ?? null;
            $rawCityId = $validated['billing_city'] ?? null;
            $rawStateId = $validated['billing_state'] ?? null;

            $cleanAreaId = (is_numeric($rawAreaId) && floatval($rawAreaId) > 0) ? intval($rawAreaId) : null;
            $cleanCityId = (is_numeric($rawCityId) && floatval($rawCityId) > 0) ? intval($rawCityId) : null;
            $cleanStateId = (is_numeric($rawStateId) && floatval($rawStateId) > 0) ? intval($rawStateId) : null;
            $cleanCountry = $validated['billing_country'] ?? 'Oman';

            // Simplified logic matching website: prioritizing most specific location
            // Use delivery_charge helper's internal fallback (Area > City > State > Country)
            $shipping_charge = delivery_charge(
                $cleanAreaId ?? $cleanCityId ?? $cleanStateId ?? $cleanCountry,
                $cleanAreaId ? 'area' : ($cleanCityId ? 'city' : ($cleanStateId ? 'state' : null))
            );
        }

        \Illuminate\Support\Facades\Log::info('WhatsApp Checkout Debug', [
            'order_source' => $validated['order_source'],
            'billing_area_id' => $validated['billing_area_id'] ?? 'null',
            'billing_city' => $validated['billing_city'] ?? 'null',
            'clean_area_id' => $cleanAreaId ?? 'null',
            'clean_city_id' => $cleanCityId ?? 'null',
            'received_shipping_override' => $validated['shipping_charge'] ?? 'none',
            'final_shipping_charge' => $shipping_charge,
        ]);

        $weight_charge = $this->calculateExtraWeightFees($validated['cart_items']);
        $grandTotal = $subtotal + $shipping_charge + $weight_charge + $tax;

        // Discount
        $discount = 0;
        if (isset($validated['coupon_code'])) {
            $coupon = \App\Models\Admin\Coupon::where('CouponCode', $validated['coupon_code'])
                ->where('Status', 1)
                ->where('ExpireDate', '>=', \Illuminate\Support\Carbon::now()->toDateString())
                ->where('Min_Expenses', '<=', $subtotal)
                ->first();

            if ($coupon) {
                $discount = $coupon->amount;
                $grandTotal -= $discount;
            }
        }

        $order_number = $this->generateOrderNumber();
        
        // Billing
        $billing = \App\Models\Admin\Billing::updateOrCreate(
            ['User_Id' => $user_id],
            [
                'Name' => $validated['billing_name'],
                'Email' => $validated['billing_email'] ?? $user->email,
                'Street' => $validated['billing_street_address'] ?? '',
                'State' => $validated['billing_state'],
                'City' => $validated['billing_city'] ?? null,
                'Zipcode' => $validated['billing_zipcode'],
                'Country' => $validated['billing_country'],
            ]
        );

        $state = \App\Models\State::find($validated['billing_state']);
        $city = \App\Models\City::find($validated['billing_city']);
        $area = isset($validated['billing_area_id']) ? \App\Models\Area::find($validated['billing_area_id']) : null;

        $billing_address = [
            'name' => $billing->Name,
            'email' => $billing->Email,
            'street' => $billing->Street,
            'state' => $billing->State,
            'city' => $billing->City,
            'area_id' => $validated['billing_area_id'] ?? null,
            'zipcode' => $billing->Zipcode,
            'country' => $billing->Country,
            'state_en' => $state->name_en ?? '',
            'state_ar' => $state->name_ar ?? '',
            'city_en' => $city->name_en ?? '',
            'city_ar' => $city->name_ar ?? '',
            'area_en' => $area->name_en ?? '',
            'area_ar' => $area->name_ar ?? '',
            'phone_number' => $user->Number ?? '',
        ];

        $payment_method = $validated['Payment_Method'];
        if ($payment_method == 'CashOnDelivery') {
            $payment_method = 'COD';
        }
        
        $collection_method = $validated['Collection_Method'] ?? 'Delivery';

        // Paid methods: Bank Transfer
        $isPaidMethod = in_array(strtoupper($payment_method), ['BANK_TRANSFER']);

        // For store pickup: override shipping to 0
        if (strtoupper($collection_method) === 'STORE_PICKUP') {
            $shipping_charge = 0;
            $grandTotal = $subtotal + $tax;
        }

        $initial_status = ORDER_PENDING;
        if ($payment_method == 'COD' || $payment_method == 'Thawani' || $isPaidMethod || strtoupper($collection_method) === 'STORE_PICKUP') {
            $initial_status = ORDER_PROCESSING;
        }

        // Create Order
        $order = \App\Models\Admin\Order::create([
            'Order_Number' => $order_number,
            'User_Id' => $user_id,
            'Billing_Id' => $billing->id,
            'billing_address' => $billing_address,
            'shipping_address' => $billing_address,
            'Delivery_Charge' => $shipping_charge,
            'Tax' => $tax,
            'Sub_Total' => $subtotal,
            'Coupon_Id' => $validated['coupon_code'] ?? null,
            'Coupon_Amount' => $discount,
            'Grand_Total' => $grandTotal,
            'Is_Free_Delivery' => false,
            'Is_Order_Successful' => false,
            'Is_Order_Completed' => false,
            'Payment_Method' => $payment_method,
            'Payment_Status' => $isPaidMethod ? PAYMENT_SUCCESS : PAYMENT_PENDING,
            'is_paid' => $isPaidMethod ? 1 : 0,
            'collection_method' => strtolower($collection_method),
            'Order_Status' => $initial_status,
            'order_source' => 'whatsapp',
        ]);

        foreach ($validated['cart_items'] as $item) {
            $product = \App\Models\Admin\Product::find($item['product_id']);
            $this->subQtyProduct($product->id, $item['quantity']);

            $sizePrice = 0;
            $sizeWeight = 0;
            if (!empty($item['size_id'])) {
                $size = $product->sizes()->where('size_product.Size_Id', $item['size_id'])->first();
                $sizePrice = $size->pivot->price ?? 0;
                $sizeWeight = $size->pivot->weight ?? 0;
            }

            $weightPrice = 0;
            $weightValue = 0;
            if (!empty($item['weight_id'])) {
                $weight = \App\Models\WeightProduct::find($item['weight_id']);
                $weightPrice = $weight->price ?? 0;
                $weightValue = $weight->weight ?? 0;
            }

            $price = $sizePrice + $weightPrice;
            if ($price == 0) {
                $price = $product->Price ?? 0;
            }
            $additions = \App\Models\Admin\Addition::whereIn('id', $item['addition_ids'] ?? [])->get();
            $price += $additions->sum('price');

            if ($product->Discount) {
                $price -= ($product->Discount / 100) * $price;
            }

            $productName = app()->getLocale() === 'ar' ? $product->fr_Product_Name : $product->en_Product_Name;

            \App\Models\Admin\OrderDetails::create([
                'Order_Id' => $order->id,
                'Product_Id' => $product->id,
                'Product_Name' => $productName,
                'Image' => $product->Primary_Image,
                'Price' => $price,
                'Size' => $sizeWeight ?: $weightValue,
                'Quantity' => $item['quantity'],
                'Total_Price' => $price * $item['quantity'],
            ]);
        }

        // Sync Order to Smart ERP immediately as UNPAID (Two-step sync approach)
        // However, do NOT sync online payments (Thawani, Stripe, etc.) if they are not paid yet.
        $isOnlinePayment = in_array(strtolower($payment_method), ['thawani', 'stripe', 'paypal']);
        $shouldSyncNow = config('smartlife.sync_enabled') && (!$isOnlinePayment || $order->is_paid);
        
        if ($shouldSyncNow) {
            try {
                $smartLifeService = app(\App\Services\SmartLifeErpService::class);
                $invoiceId = $smartLifeService->submitOrder($order);
                if ($invoiceId) {
                    $order->smartlife_synced_at = now();
                    $order->smartlife_invoice_id = $invoiceId;
                    $order->save();
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('SmartLife sync failed in WhatsApp checkout', ['error' => $e->getMessage()]);
            }
        }

        // Generate Thawani Session only for online payment
        if ($validated['Payment_Method'] == 'Thawani') {
            return $this->generateThawaniSession($order, $user);
        }

        // For COD, Bank Transfer, In-Store Pickup: trigger notifications
        event(new \App\Events\OrderCreated($order));

        return response()->json([
            'message' => 'Order created successfully',
            'order_number' => $order_number,
            'grand_total' => $order->Grand_Total,
            'payment_method' => $validated['Payment_Method'],
            'language' => app()->getLocale(),
            'receipt_url' => route('order.print', ['id' => $order->id, 'lang' => app()->getLocale()]),
            'invoice_pdf_url' => route('api.whatsapp.invoice_pdf', ['id' => $order->id, 'lang' => app()->getLocale()])
        ]);
    }

    protected function calculateSubtotal(array $cartItems)
    {
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $product = \App\Models\Admin\Product::find($item['product_id']);
            $sizePrice = 0;
            if (!empty($item['size_id'])) {
                $size = $product->sizes()->where('size_product.Size_Id', $item['size_id'])->first();
                $sizePrice = $size->pivot->price ?? 0;
            }
            $weightPrice = 0;
            if (!empty($item['weight_id'])) {
                $weight = \App\Models\WeightProduct::find($item['weight_id']);
                $weightPrice = $weight->price ?? 0;
            }
            $additions = \App\Models\Admin\Addition::whereIn('id', $item['addition_ids'] ?? [])->get();
            $additionPrice = $additions->sum('price');

            $basePrice = $sizePrice + $weightPrice;
            if ($basePrice == 0) {
                $basePrice = $product->Price ?? 0;
            }
            $price = $basePrice + $additionPrice;
            if ($product->Discount) {
                $price -= ($product->Discount / 100) * $price;
            }
            $subtotal += $price * $item['quantity'];
        }
        return $subtotal;
    }

    protected function calculateExtraWeightFees(array $cartItems)
    {
        $totalWeightGrams = 0;
        foreach ($cartItems as $item) {
            $product = \App\Models\Admin\Product::find($item['product_id']);
            $sizeWeight = 0;
            if (!empty($item['size_id'])) {
                $size = $product->sizes()->where('size_product.Size_Id', $item['size_id'])->first();
                $sizeWeight = $size->pivot->weight ?? 0;
            }
            $weightValue = 0;
            if (!empty($item['weight_id'])) {
                $weight = \App\Models\WeightProduct::find($item['weight_id']);
                $weightValue = $weight->weight ?? 0;
            }
            $totalWeightGrams += ($sizeWeight + $weightValue) * $item['quantity'];
        }

        $totalWeightKg = $totalWeightGrams / 1000;
        $fee = 0;
        if ($totalWeightKg > 25) { // Assuming 25kg free limit
            $extraKg = ceil($totalWeightKg - 25);
            $fee = $extraKg * 0.100;
        }
        return $fee;
    }

    protected function generateOrderNumber()
    {
        // Use a transaction-safe lock if possible, but for simple incremental:
        $maxOrderId = \App\Models\Admin\Order::max('id') ?? 0;
        $nextId = $maxOrderId + 1;
        $orderNumber = (string)(10000 + $nextId);

        // Double check uniqueness to prevent collisions
        while (\App\Models\Admin\Order::where('Order_Number', $orderNumber)->exists()) {
            $nextId++;
            $orderNumber = (string)(10000 + $nextId);
        }

        return $orderNumber;
    }

    protected function subQtyProduct($product_id, $qty)
    {
        $product = \App\Models\Admin\Product::find($product_id);
        if ($product) {
            $product->update(['Quantity' => max(0, $product->Quantity - $qty)]);
        }
    }

    protected function generateThawaniSession($order, $user)
    {
        $paymentData = [
            'client_reference_id' => $order->Order_Number,
            'mode' => 'payment',
            'products' => [
                [
                    'name' => 'Order ' . $order->Order_Number,
                    'quantity' => 1,
                    'unit_amount' => (int) round($order->Grand_Total * 1000), // Ensures an integer in baisa
                ]
            ],
            'success_url' => route('api.thawani.success', ['order_number' => $order->Order_Number, 'phone_number' => $user->Number]),
            'cancel_url' => route('api.thawani.fail', ['order_number' => $order->Order_Number]),
        ];

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
            'thawani-api-key' => config('services.thawani.secret_key')
        ])->post(config('services.thawani.checkout_url') . '/checkout/session', $paymentData);

        if ($response->successful()) {
            $sessionId = $response['data']['session_id'] ?? null;
            if ($sessionId) {
                $order->update(['session_id' => $sessionId]);
                
                \App\Models\PaymentModel::create([
                    'session_id' => $sessionId,
                    'user_id' => $user->id,
                    'order_number' => $order->Order_Number,
                    'amount' => $order->Grand_Total,
                    'status' => 'CREATED',
                ]);

                $paymentUrl = config('services.thawani.pay_url') . $sessionId . "?key=" . config('services.thawani.public_key');

                // Dispatch a delayed reminder job (fires after 30 min, only if still PENDING)
                // This avoids interrupting customers who are currently completing their payment.
                \App\Jobs\SendPendingThawaniReminderJob::dispatch($order->id, $paymentUrl)
                    ->delay(now()->addMinutes(5));

                return response()->json([
                    'message' => 'Payment session created',
                    'order_id' => $order->id,
                    'order_number' => $order->Order_Number,
                    'grand_total' => $order->Grand_Total,
                    'payment_method' => 'thawani',
                    'url' => $paymentUrl,
                    'language' => app()->getLocale(),
                    'invoice_pdf_url' => route('api.whatsapp.invoice_pdf', ['id' => $order->id, 'lang' => app()->getLocale()])
                ]);
            }
        }

        \Illuminate\Support\Facades\Log::error('Thawani API Checkout Error', ['body' => $response->body(), 'status' => $response->status()]);
        return response()->json(['error' => 'Payment gateway error', 'details' => $response->json() ?? $response->body()], 500);
    }

    /**
     * Generate actual PDF for WhatsApp Invoice (Optimized for mPDF)
     */
    public function getOrderInvoicePdf(\Illuminate\Http\Request $request, $id)
    {
        // Use requested language (from WhatsApp link) or fallback to app locale
        if ($request->has('lang')) {
            app()->setLocale($request->lang);
        }

        $order = \App\Models\Admin\Order::with(['order_details', 'user', 'billing', 'order_details.product'])->find($id);
        
        if (!$order) {
            abort(404, 'Order not found');
        }

        $order['billing_address'] = $order->billing_address;

        $pdf = \PDF::loadView('admin.pages.orders.pdf_invoice', compact('order'), [], [
            'mode'                 => 'utf-8',
            'format'               => 'A4-P',
            'autoScriptToLang'     => true,
            'autoArabic'           => true,
            'margin_left'          => 10,
            'margin_right'         => 10,
            'margin_top'           => 10,
            'margin_bottom'        => 10,
        ]);

        return $pdf->stream('invoice-' . $order->Order_Number . '.pdf');
    }

    /**
     * Get last order details for authenticated user
     */
    public function getLastOrder()
    {
        $order = \App\Models\Admin\Order::where('User_Id', auth()->id())->latest()->first();
        
        if (!$order) {
            return response()->json(['message' => 'No orders found'], 404);
        }

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->Order_Number,
                'grand_total' => $order->Grand_Total,
                'order_status' => $order->Order_Status,
                'created_at' => $order->created_at?->toDateTimeString(),
                'invoice_pdf_url' => route('api.whatsapp.invoice_pdf', ['id' => $order->id])
            ]
        ]);
    }

    /**
     * Handle actions from WhatsApp bot (e.g. Convert to COD, Cancel Order)
     */
    public function handleOrderAction(\Illuminate\Http\Request $request)
    {
        \Illuminate\Support\Facades\Log::info('WhatsApp Order Action Received', ['data' => $request->all()]);

        try {
            $validated = $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'action' => 'required|string|in:convert_to_cod,cancel_order',
            ]);

            $order = \App\Models\Admin\Order::find($validated['order_id']);

            if ($validated['action'] === 'convert_to_cod') {
                if (strtoupper($order->Payment_Method) === 'THAWANI' && $order->Payment_Status === 'PENDING') {
                    $order->Payment_Method = 'COD';
                    $order->Order_Status = defined('ORDER_PROCESSING') ? ORDER_PROCESSING : 2;
                    $order->save();

                    \Illuminate\Support\Facades\Log::info('Order converted to COD via WhatsApp', ['order_id' => $order->id]);

                    // Trigger standard notification
                    app(\App\Http\Controllers\Frontend\CheckoutController::class)->sendOrderNotification($order->id);

                    return response()->json(['message' => 'Order converted to COD and notification sent.']);
                }
                return response()->json(['message' => 'Order cannot be converted. Status might be paid or already COD.'], 400);
            }

            if ($validated['action'] === 'cancel_order') {
                if ($order->is_paid == 1 || $order->Payment_Method == 'COD') {
                    \Illuminate\Support\Facades\Log::warning('WhatsApp Cancel Attempt Failed: Order already paid or COD', ['order_id' => $order->id]);
                    return response()->json(['message' => 'Paid or COD orders cannot be cancelled via WhatsApp. Please contact support.'], 400);
                }

                if (strtolower($order->Payment_Method) === 'thawani' && $order->is_paid == 0) {
                    \App\Models\Admin\OrderDetails::where('Order_Id', $order->id)->delete();
                    $order->delete();
                    \Illuminate\Support\Facades\Log::info('Order deleted via WhatsApp because it was unpaid thawani', ['order_id' => $order->id]);
                    return response()->json(['message' => 'Order deleted successfully.']);
                }

                $cancelledStatus = defined('ORDER_CANCELLED') ? ORDER_CANCELLED : 5;
                $order->Order_Status = $cancelledStatus;
                $order->save();

                \Illuminate\Support\Facades\Log::info('Order cancelled via WhatsApp', ['order_id' => $order->id]);

                return response()->json(['message' => 'Order cancelled successfully.']);
            }
        } catch (\Illuminate\Validation\ValidationException $ve) {
            \Illuminate\Support\Facades\Log::error('WhatsApp Order Action Validation Error', ['errors' => $ve->errors(), 'data' => $request->all()]);
            return response()->json(['message' => 'Validation failed', 'errors' => $ve->errors()], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp Order Action Exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Server error occurred'], 500);
        }

        return response()->json(['message' => 'Invalid action'], 400);
    }
}
