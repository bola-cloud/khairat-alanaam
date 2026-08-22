<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use App\Http\Requests\CheckoutOrderRequest;
use App\Http\Services\BasePaymentService;
use App\Http\Services\InstamojoService;
use App\Http\Services\PaymentService;
// use App\Jobs\OrderConfirmMail;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Models\Admin\Billing;
use App\Models\Admin\Color;
use App\Models\Admin\Coupon;
use App\Models\Admin\Order;
use App\Models\Admin\OrderDetails;
use App\Models\Admin\Product;
use App\Models\Admin\Shipping;
use App\Models\Admin\Size;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\PaymentPlatform;
use App\Models\SeoSetting;
use App\Models\Setting;
use App\Models\State;
use App\Models\WeightProduct;
use App\Resolvers\PaymentPlatformResolver;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmMail;
use Illuminate\Support\Str;
use App\Models\Offer;
use App\Models\User;
use App\Jobs\SyncSmartLifeOrder;

class CheckoutController extends Controller
{
    // Free weight allowance in kilograms before extra weight fees apply
    const FREE_WEIGHT_LIMIT_KG = 25;

    private $grand_total;
    private $discount;
    protected $paymentPlatformResolver;
    protected $paymentController;



    public function __construct(PaymentPlatformResolver $paymentPlatformResolver, PaymentController $paymentController)
    {
        $this->discount = 0;
        $this->paymentPlatformResolver = $paymentPlatformResolver;
        $this->paymentController = $paymentController;
    }

    public function calculateExtraWeightFees()
    {
        $totalWeightGrams = 0;
        foreach (Cart::content() as $item) {
            $itemWeight = $item->options->weight->weight ?? 0;
            $totalWeightGrams += $itemWeight * $item->qty;
        }
        $totalWeightKg = $totalWeightGrams / 1000;

        $shippingFee = 0;
        if ($totalWeightKg > self::FREE_WEIGHT_LIMIT_KG) {
            $extraKg = ceil($totalWeightKg - self::FREE_WEIGHT_LIMIT_KG);
            $shippingFee = ($extraKg * 0.100);
        }

        return $shippingFee;
    }

    public function checkoutPage()
    {
        $min_order_amount = floatval(allsetting('min_order_amount') ?: 3.990);
        if (subtotal() < $min_order_amount) {
            return redirect()->route('cart.content')->with('toast_error', __('Minimum order amount is :amount OMR.', ['amount' => number_format($min_order_amount, 3)]));
        }

        // Require login before showing the checkout page. Guests should not be able
        // to open the checkout page (use guestCheckoutOrder if guest checkout is enabled).
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('toast_warning', __('Please login to proceed to checkout'));
        }

        $check = Cart::count();
        if ($check) {
            $data['content'] = Cart::content();
            $data['currencies'] = Currency::all();
            $data['paymentPlatforms'] = PaymentPlatform::where('status', ACTIVE)->get();
            $data['user'] = Auth::user();
            $data['billing'] = Billing::where('User_Id', Auth::id())->first() ?? Auth::user();
            $data['shipping'] = Shipping::where('User_Id', Auth::id())->first();
            $seo = SeoSetting::where('slug', 'checkout')->first();
            $data['title'] = $seo->title;
            $data['description'] = $seo->description;
            $data['keywords'] = $seo->keywords;
            $data['extraWeightFees'] = $this->calculateExtraWeightFees();
            $oman = Country::where('name_en', 'Oman')->first();
            if ($oman) {
                $oman_country_id = $oman->id;
            } else {
                $firstCountry = Country::first();
                $oman_country_id = $firstCountry ? $firstCountry->id : null;
            }

            $data['saved_addresses'] = $data['user']->addresses()->orderBy('is_default', 'desc')->get();

            // If we have a country id, load states; otherwise give an empty collection to avoid null access in views.
            $data['states'] = $oman_country_id ? State::where('country_id', $oman_country_id)->get() : collect([]);
            $data['users'] = User::where(['is_admin' => 0, 'status' => 1])
                // ->with('billing')
                // ->select('id', 'name', 'number')
                ->get();

            $offers = Offer::where('type', 'buy_x_get_z')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->where('status', 1)
                ->get();

            $cartProductIds = $data['content']->pluck('id')->toArray();

            foreach ($offers as $offer) {
                $requiredProductIds = $offer->required_product_ids;
                $giftProductIds = $offer->gift_product_ids;

                if (!is_array($requiredProductIds) || !is_array($giftProductIds)) {
                    continue;
                }

                $found = array_intersect($requiredProductIds, $cartProductIds);

                if (!empty($found)) {
                    foreach ($giftProductIds as $giftProductId) {
                        $giftExists = Cart::search(fn($cartItem, $rowId) => $cartItem->id == $giftProductId)->isNotEmpty();
                        if (!$giftExists) {
                            $giftProduct = Product::find($giftProductId);
                            if ($giftProduct) {
                                $color_id = DB::table('color_product')->where('Product_Id', $giftProduct->id)->first();
                                $size_id = DB::table('size_product')->where('Product_Id', $giftProduct->id)->first();
                                $selected_size = DB::table('size_product')->where('Product_Id', $giftProduct->id)->first();
                                $selected_weight = WeightProduct::where('product_id', $giftProduct->id)->first();
                                $color_name = Color::where('id', $color_id?->color_id)->first();
                                $size_name = Size::where('id', $size_id?->size_id)->first();

                                Cart::add([
                                    'id' => $giftProduct->id,
                                    'name' => $giftProduct->en_Product_Name,
                                    'qty' => 1,
                                    'price' => 0,
                                    'size' => $size_id == 0 ? $size_id : $size_name->Size,
                                    'selectedSize' => $request->selectedSize ?? null,
                                    'selectedWeight' => $selected_weight ?? null,
                                    'weight' => $selected_size->weight ?? 0,
                                    'options' => [
                                        'name_ar' => $giftProduct->fr_Product_Name,
                                        'additions' => $additions ?? [],
                                        'size' => $size_id == 0 ? $size_id : $size_name->Size,
                                        'size_ar' => $size_id == 0 ? $size_id : $size_name->Size_ar,
                                        'color' => $color_id == 0 ? $color_id : $color_name->ColorCode,
                                        'image' => $giftProduct->Primary_Image,
                                        'weight' => $selected_weight ?? null,
                                        'slug' => $giftProduct->en_Product_Slug,
                                        'discount_price' => 0,
                                        'item_tag' => $giftProduct->ItemTag,
                                        'discount_parcent' => 100,
                                        'voucher' => $giftProduct->Voucher,
                                    ]
                                ]);
                            }
                        }
                    }
                    session()->flash('success', ('تم اضافة منتج مجاني من العروض'));
                    return view('front.home.checkout', $data);
                }
            }
            return view('front.home.checkout', $data);
        } else {
            return redirect()->route('front')->with('toast_warning', 'Cart is Empty');
        }
    }

    public function thankyouPage()
    {
        $data['title'] = __('Order Confirmed');
        $data['description'] = __('Order Confirmed');
        $data['keywords'] = __('Order Confirmed');
        return view('front.pages.checkout.thankyou', $data);
    }
    public function checkoutOrder(Request $request)
    {
        Log::info('Checkout Request Data', ['data' => $request->all(), 'user_id' => Auth::id()]);
        $isLoggedIn = Auth::check();
        // $user_id = $isLoggedIn ? Auth::id() : null;
        $buy_for = null;
        $admin_id = null;

        if ($isLoggedIn) {
            if (Auth::user() && Auth::user()->is_admin) {
                $user_id = $request->user_id;
                $buy_for = $request->user_id;
                $admin_id = Auth::id();
            } else {
                $user_id = Auth::id();
            }
        } else {
            $user_id = null;
        }

        // dd($request->all(), $isLoggedIn, Auth::user()->is_admin, $user_id);

        $isPickup = $request->collection_method === 'store_pickup';
        
        // Validation
        $validationRules = [
            'collection_method' => 'required|in:delivery,store_pickup',
            'payment' => 'required',
            'billing_name' => 'required',
            'billing_email' => 'nullable|email',
            'billing_street_address' => 'nullable',
            'billing_zipcode' => 'required',
            'billing_country' => 'required',
            'billing_city' => $isPickup ? 'nullable' : 'required',
            'billing_area' => $isPickup ? 'nullable' : 'required',
            'billing_state' => $isPickup ? 'nullable' : 'required',
            "billing_phone" => 'required|regex:/^\+?[0-9]{8,15}$/',
        ];

        $validationMessages = [
            'billing_phone.required' => __('The phone number field is required.'),
            'billing_phone.digits_between' => __('The phone number must be correct number.'),
        ];
        // dd($request->all());

        if (!$isLoggedIn) {
            $validationRules += [
                'billing_name' => 'required',
                'billing_state' => 'required',
                'billing_country' => 'required',
                'billing_zipcode' => 'required',
            ];

            $validationMessages += [
                'billing_name.required' => __('The name field is required.'),
                'billing_state.required' => __('The state field is required.'),
                'billing_zipcode.required' => __('The zipcode field is required.'),
                'billing_country.required' => __('The country field is required.'),
            ];
        }

        // Final Stock Validation
        foreach (Cart::content() as $cItem) {
            $product = Product::find($cItem->id);
            if ($product) {
                $available = $product->virtual_stock;
                if ($cItem->qty > $available) {
                    return redirect()->back()->with('toast_error', __('Stock exceeded for product: ') . $product->en_Product_Name . '. Available: ' . $available);
                }
            }
        }

        $request->validate($validationRules, $validationMessages);

        $min_order_amount = floatval(allsetting('min_order_amount') ?: 3.990);
        if (subtotal() < $min_order_amount) {
            return redirect()->route('cart.content')->with('toast_error', __('Minimum order amount is :amount OMR.', ['amount' => number_format($min_order_amount, 3)]));
        }

        try {
            $subtotal = subtotal();
            $cartItems = Cart::content();
            if (Cart::countItems() == 0) {
                return redirect()->route(route: 'front')->with('error', __('Cart is empty. Go to product page and cart something.'));
            }
            // Resolve billing country to a country name if an ID was provided so tax lookup succeeds
            $countryParam = $request->billing_country;
            $countryName = null;
            if (is_numeric($countryParam)) {
                $countryModel = Country::find($countryParam);
                if ($countryModel) {
                    $countryName = $countryModel->name_en ?? $countryModel->name;
                }
            } else {
                $countryName = $countryParam;
            }
            $countryParam = $request->billing_country;
            $tax = tax_amount($subtotal, $countryParam);
            
            $shipping_charge = delivery_charge($request->billing_area ?? $request->billing_city ?? $request->billing_state ?? $request->billing_country, ($request->billing_area) ? 'area' : null);
            $weight_charge = $this->calculateExtraWeightFees();
            $shipping_charge += $weight_charge;

            // Log shipping calculation for debugging delivery charge issues
            Log::info('Shipping calculation', [
                'user_id' => Auth::id(),
                'billing_city' => $request->billing_city ?? null,
                'billing_area' => $request->billing_area ?? null,
                'billing_state' => $request->billing_state ?? null,
                'billing_country' => $request->billing_country ?? null,
                'subtotal' => $subtotal,
                'base_delivery_charge' => delivery_charge($request->billing_area ?? $request->billing_city ?? $request->billing_state ?? $request->billing_country, ($request->billing_area) ? 'area' : null),
                'weight_charge' => $weight_charge,
                'shipping_charge_total' => $shipping_charge,
            ]);

            $free_shipping = Setting::where('slug', 'free_shipping')->value('value') ?? 0;

            $free_shipping_offer = Offer::where('type', 'free_shipping_with_total_bill')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->where('status', 1)
                ->where('minimum_total', '<=', $subtotal)
                ->first();

            if ($free_shipping_offer) {
                $shipping_charge = 0;
            }

            if ($free_shipping == 1) {
                $shipping_charge = 0;
            }


            // Determine any "total bill" offers first so $this->discount is known
            $offers = Offer::where('type', "total_bill_discount")->orderBy('minimum_total', 'desc')->get();
            foreach ($offers as $offer) {
                if ($subtotal >= $offer->minimum_total) {
                    $this->discount = $offer->discount_value;
                    break;
                }
            }

            // Apply any percentage discount (offers/coupons) to subtotal
            $discountAmount = 0;

            if ($isLoggedIn && !$admin_id) {
                $activeSubscription = \App\Models\UserSubscription::where('user_id', $user_id)
                    ->where('status', 'active')
                    ->whereDate('end_at', '>=', now())
                    ->with('subscription')
                    ->latest()
                    ->first();

                // \Log::info('Checkout Subscription Check', [
                //     'user_id' => $user_id,
                //     'found' => $activeSubscription ? true : false,
                //     'sub_id' => $activeSubscription ? $activeSubscription->id : null,
                //     'status' => $activeSubscription ? $activeSubscription->status : 'N/A'
                // ]);

                if ($activeSubscription && $activeSubscription->subscription) {
                    if ($activeSubscription->subscription->discount_percent > 0) {
                        $subscriptionDiscountPercent = $activeSubscription->subscription->discount_percent;
                        $maxDiscountAmount = $activeSubscription->subscription->max_discount_amount ?? PHP_INT_MAX;

                        $calculatedDiscount = ($subscriptionDiscountPercent / 100) * $subtotal;
                        $subscriptionDiscount = min($calculatedDiscount, $maxDiscountAmount);
                        $discountAmount += $subscriptionDiscount;

                        session()->put('subscription_discount_percent', $subscriptionDiscountPercent);
                        session()->put('subscription_discount_amount', $subscriptionDiscount);

                        \Log::info("Subscription discount applied", [
                            'user_id' => $user_id,
                            'subscription_id' => $activeSubscription->subscription_id,
                            'discount_percent' => $subscriptionDiscountPercent,
                            'calculated_discount' => $calculatedDiscount,
                            'max_discount' => $maxDiscountAmount,
                            'applied_discount' => $subscriptionDiscount
                        ]);
                    }

                    if ($activeSubscription->subscription->free_shipping) {
                        $shipping_charge = 0;
                        session()->put('free_shipping_applied', true);
                        \Log::info("Free shipping applied from subscription", [
                            'user_id' => $user_id,
                            'subscription_id' => $activeSubscription->subscription_id,
                        ]);
                    }
                }
            }

            if (!empty($this->discount) && is_numeric($this->discount) && $this->discount > 0) {
                // $this->discount is treated as a percentage for offers
                $discountAmount += ($this->discount / 100) * $subtotal;
            }

            // Include tax in grand total and subtract discounts; ensure shipping_charge included
            $this->grand_total = $subtotal + $tax + $shipping_charge - $discountAmount;


            $shipping_city = City::find($request->billing_city);
            $shipping_area = \App\Models\Area::find($request->billing_area);
            $shipping_state = State::find($request->billing_state);

            // Address handling
            if ($isLoggedIn && is_null($admin_id)) {
                if (hasBlillingAddress($user_id) == 1) {
                    $billing_create = $this->updateBillingAddress($request, $user_id);
                } else {
                    $billing_create = $this->createBillingAddress($request, $user_id);
                }

                $billing_address = [
                    'name' => $billing_create->Name,
                    'email' => $billing_create->Email,
                    'street' => $billing_create->Street,
                    'state' => $billing_create->State,
                    'city' => $billing_create->City,
                    'area' => $request->billing_area,
                    'zipcode' => $billing_create->Zipcode,
                    'country' => $billing_create->Country,
                ];

                $shipping_address = $billing_address;
            } else {
                $billing_address = [
                    'name' => $request->billing_name,
                    'email' => $request->billing_email,
                    'street' => $request->billing_street_address,
                    'state' => $request->billing_state,
                    'city' => $request->billing_city,
                    'area' => $request->billing_area,
                    'zipcode' => $request->billing_zipcode,
                    'country' => $request->billing_country,
                ];

                $shipping_address = [
                    'name' => $request->shipping_name,
                    'email' => $request->shipping_email,
                    'street' => $request->shipping_street_address,
                    'state' => $request->shipping_state,
                    'city' => $request->shipping_city,
                    'area' => $request->billing_area,
                    'zipcode' => $request->shipping_zipcode,
                    'country' => $request->shipping_country
                ];
            }

            $billing_address['state_en'] = $shipping_state->name_en ?? '';
            $billing_address['state_ar'] = $shipping_state->name_ar ?? '';
            $billing_address['city_en'] = $shipping_city->name_en ?? '';
            $billing_address['city_ar'] = $shipping_city->name_ar ?? '';
            $billing_address['area_en'] = $shipping_area->name_en ?? '';
            $billing_address['area_ar'] = $shipping_area->name_ar ?? '';
            $phoneNumber = $request->billing_phone ?? $request->phone_number;
            $billing_address['phone_number'] = $phoneNumber;

            $shipping_address['state_en'] = $shipping_state->name_en ?? '';
            $shipping_address['state_ar'] = $shipping_state->name_ar ?? '';
            $shipping_address['city_en'] = $shipping_city->name_en ?? '';
            $shipping_address['city_ar'] = $shipping_city->name_ar ?? '';
            $shipping_address['area_en'] = $shipping_area->name_en ?? '';
            $shipping_address['area_ar'] = $shipping_area->name_ar ?? '';
            $shipping_address['phone_number'] = $phoneNumber;


            Session::put('billing_address', $billing_address);
            Session::put('shipping_address', $shipping_address);
            Session::put('checkout_email', $billing_address['email']);
            Session::put('collection_method', $request->collection_method);
            Session::put('is_gift', $request->has('is_gift') ? 1 : 0);
            Session::put('gift_recipient_name', $request->input('gift_recipient_name'));
            Session::put('gift_recipient_phone', $request->input('gift_recipient_phone'));
            Session::put('gift_message', $request->input('gift_message'));

            if ($isLoggedIn && is_null($admin_id)) {
                Session::put('billing_id', $billing_create->id);
                Session::put('shipping_id', $billing_create->id);
            }

            // Generate unique sequential order number (numeric only)
            $order_number = $this->generateOrderNumber();

            // Coupon handling
            if ($isLoggedIn && Session::has('Coupon_Id')) {
                $coupon = Coupon::whereId(Session::get('Coupon_Id'))->first();
                if ($coupon) {
                    if (is_null($admin_id)) {
                        $orderCoupon = Order::where('Coupon_Id', $coupon->id)->where('User_Id', $user_id)->count();
                        if ($orderCoupon != 0) {
                            session()->put('couponCode', null);
                            return redirect()->back()->with('error', 'Already used coupon Code');
                        }
                    }

                    $this->discount = $coupon->Amount;
                }
            }

            // Set session variables
            session()->put('order_number', $order_number);
            session()->put('shipping_charge', $shipping_charge);
            session()->put('subtotal', $subtotal);
            // keep discount as percentage in session and also store computed discount amount
            session()->put('discount', $this->discount);
            session()->put('discount_amount', $discountAmount ?? 0);
            session()->put('grand_total', $this->grand_total);
            session()->put('tax', $tax);

            // Payment processing
            // Initialize wallet session (default 0)
            session()->put('wallet_used', 0);
            session()->put('wallet_remaining_balance', 0);

            // Wallet Logic
            $wallet_used = 0;
            if ($isLoggedIn && is_null($admin_id)) {
                $user = Auth::user();
                $wallet_balance = $user->balance;

                if ($wallet_balance > 0) {
                    if ($wallet_balance >= $this->grand_total) {
                        $wallet_used = $this->grand_total;
                        $this->grand_total = 0; // Fully paid by wallet
                    } else {
                        $wallet_used = 0;
                        // $this->grand_total remains full (no partial payment)
                    }

                    session()->put('wallet_used', $wallet_used);
                    session()->put('wallet_remaining_balance', $wallet_balance - $wallet_used);

                    // Update grand_total in session for payment gateways
                    session()->put('grand_total', $this->grand_total);
                }
            }

            // Check if fully paid by wallet
            if ($wallet_used > 0 && $this->grand_total <= 0) {
                return $this->orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $this->discount, 0, 'WALLET');
            }

            switch ($request->payment) {
                case 'creditcard':
                    session()->put('payment_method_name', STRIPE);
                    return $this->pay($this->grand_total * (conversion_rate('USD') ? conversion_rate('USD') : 0), $this->discount, 'USD', 2, $request->payment_method);

                case 'paypal':

                    $checkoutProduct = [];
                    $totalItems = Cart::countItems();
                    $discountAmount = ($this->discount / 100) * $subtotal;

                    foreach ($cartItems as $item) {
                        $unit_amount = $item->price;
                        if ($unit_amount != 0) {
                            $itemTotalPrice = $unit_amount * $item->qty;
                            $itemDiscount = ($itemTotalPrice / $subtotal) * $discountAmount;
                            $newUnitAmount = $unit_amount - ($itemDiscount / $item->qty);
                            $cleanName = preg_replace('/[^A-Za-z0-9\s\x{0600}-\x{06FF}]/u', '', $item->name);
                            $checkoutProduct[] = [
                                'name' => Str::limit($cleanName, 35),
                                'quantity' => $item->qty,
                                'unit_amount' => number_format($newUnitAmount, 3) * 1000,
                            ];
                        }
                    }
                    // dd($checkoutProduct);
                    if ($shipping_charge) {
                        $checkoutProduct[] = [
                            'name' => 'Shipping Charge',
                            'quantity' => 1,
                            'unit_amount' => number_format($shipping_charge, 3) * 1000,
                        ];
                    }

                    if ($tax > 0) {
                        $checkoutProduct[] = [
                            'name' => 'Tax',
                            'quantity' => 1,
                            'unit_amount' => number_format($tax, 3) * 1000,
                        ];
                    }

                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'thawani-api-key' => config('services.thawani.secret_key'),
                    ])->post(config('services.thawani.checkout_url') . '/checkout/session', [
                                'client_reference_id' => $order_number,
                                'mode' => 'payment',
                                'products' => $checkoutProduct,
                                'success_url' => route('thawani.success', [
                                    'order_number' => $order_number,
                                ]),
                                'cancel_url' => route('thawani.cancel', [
                                    'order_number' => $order_number,
                                ]),
                                'metadata' => [
                                    'order_number' => $order_number,
                                    'shipping_charge' => $shipping_charge,
                                    'subtotal' => $subtotal,
                                    'discount' => $this->discount,
                                    'grand_total' => $this->grand_total,
                                    'tax' => $tax,
                                ]
                            ]);


                    if ($response->successful()) {
                        $paymentJsonData = $response->json();

                        // create new request body for create payment
                        $payment = [
                            'session_id' => $paymentJsonData['data']['session_id'],
                            'user_id' => $user_id,
                            'admin_id' => $admin_id,
                            'order_number' => $order_number,
                            'amount' => $this->grand_total,
                            'status' => 'CREATED',
                        ];


                        $paymentRequest = new Request($payment);


                        // create payment
                        $this->paymentController->createPayment($paymentRequest);

                        $paymentUrl = config('services.thawani.pay_url') . $paymentJsonData['data']['session_id'] . '?key=' . config('services.thawani.public_key');
                        info("paymentUrl: ", [
                            'paymentUrl' => $paymentUrl,
                            'session_id' => $paymentJsonData['data']['session_id'],
                            'order_number' => $order_number,
                            'user_id' => $user_id,
                            'admin_id' => $admin_id,
                        ]);
                        $this->orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $this->discount, $this->grand_total, "THAWANI", "PENDING", $buy_for, false);

                        // Fetch the created order to dispatch a delayed reminder
                        // The job fires after 30 minutes, but ONLY if the order is still PENDING.
                        // This avoids interrupting customers who are currently completing their payment.
                        $createdOrder = Order::where('Order_Number', $order_number)->first();
                        if ($createdOrder) {
                            \App\Jobs\SendPendingThawaniReminderJob::dispatch($createdOrder->id, $paymentUrl)
                                ->delay(now()->addMinutes(5));
                        }


                        // if ($admin_id) {
                        //     $order = Order::where('Order_Number', $order_number)->where('admin_id', $admin_id)->where('User_Id', $user_id)->first();
                        //     $serialized_billing = json_decode($order->billing_address);

                        //     $phoneNumber = null;
                        //     if (isset($serialized_billing->phone_number)) {
                        //         $phoneNumber = $serialized_billing->phone_number;
                        //     }

                        //     $pdfUrl = route('order.print', ['id' => $order->id]);
                        //     $response = Http::asForm()->post('https://whatsapi.hispeed.om/api/v1/whatsapp/payment_pdf', [
                        //         'phone_number' => $phoneNumber,
                        //         'payment_url' => $paymentUrl,
                        //         'created_by' =>  'admin',
                        //         'pdf' => $pdfUrl,
                        //         'price' => $order->Grand_Total,
                        //         'language' => session('APP_LOCALE') == 'fr' ? 'ar' : 'en'
                        //     ]);



                        //     if ($response->successful()) {

                        //         return redirect()->route('front')->with('success', 'Order Created successfully');
                        //     } else {
                        //         return redirect()->back()->with('error', __('Something went wrong!'));
                        //     }
                        // }


                        return redirect()->away($paymentUrl);
                    } else {
                        // Handle the error case
                        return response()->json(['error' => 'Failed to create session' . $response], 500);
                    }

                case 'COD':
                    return $this->orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $this->discount, $this->grand_total, COD);

                case 'bank':
                    if ($request->bank_transaction_number != null) {
                        return $this->orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $this->discount, $this->grand_total, BANK_TRANSFER, $request->bank_transaction_number);
                    } else {
                        $modal = [
                            'line1' => __('Ooops! Something went wrong.'),
                            'line2' => __('Bank Transaction Number is Required.'),
                            'action' => route('checkout')
                        ];
                        return redirect()->route('checkout')->with('order_error_modal', $modal);
                    }

                case 'store_pickup':
                    return $this->orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $this->discount, $this->grand_total, 'STORE_PICKUP');

                default:
                    $modal = [
                        'line1' => __('Ooops! Something went wrong.'),
                        'line2' => __('Payment method is required'),
                        'action' => route('checkout')
                    ];
                    return redirect()->route('checkout')->with('order_error_modal', $modal);
            }
        } catch (\Exception $e) {
            info($e);
            $modal = [
                'line1' => __('Ooops! Something went wrong.'),
                'line2' => $e->getMessage() ?: __('Something went wrong'),
                'action' => route('checkout')
            ];
            return redirect()->route('checkout')->with('order_error_modal', $modal);
        }

    }
    public function guestCheckoutOrder(Request $request)
    {
        $request->validate([
            'billing_name' => 'required',
            'billing_email' => 'required|email',
            'billing_street_address' => 'required',
            'billing_zipcode' => 'required',
            'billing_country' => 'required',

            'shipping_name' => 'required',
            'shipping_email' => 'required|email',
            'shipping_street_address' => 'required',
            'shipping_state' => 'required',
            'shipping_zipcode' => 'required',
            'shipping_country' => 'required',
        ], [
            'shipping_name' => 'The name field is required.',
            'shipping_email' => 'The email field is required.',
            'shipping_street_address' => 'The address field is required.',
            'shipping_state' => 'The state field is required.',
            'shipping_zipcode' => 'The zip code field is required.',
            'shipping_country' => 'The country field is required.',
        ]);

        $billing_address = [
            'name' => $request->billing_name,
            'email' => $request->billing_email,
            'street' => $request->billing_street_address,
            'state' => $request->billing_state,
            'zipcode' => $request->billing_zipcode,
            'country' => $request->billing_country,
        ];

        $shipping_addresss = [
            'name' => $request->shipping_name,
            'email' => $request->shipping_email,
            'street' => $request->shipping_street_address,
            'state' => $request->shipping_state,
            'zipcode' => $request->shipping_zipcode,
            'country' => $request->shipping_country
        ];
        Session::put('billing_address', $billing_address);
        Session::put('shipping_address', $shipping_addresss);
        Session::put('checkout_email', $request->billing_email);
        Session::put('is_gift', $request->has('is_gift') ? 1 : 0);
        Session::put('gift_recipient_name', $request->input('gift_recipient_name'));
        Session::put('gift_recipient_phone', $request->input('gift_recipient_phone'));
        Session::put('gift_message', $request->input('gift_message'));

        $subtotal = subtotal();
        // Resolve billing country for guests as well (ID -> name) to calculate tax correctly
        $guestCountryParam = $request->billing_country;
        $guestCountryName = null;
        if (is_numeric($guestCountryParam)) {
            $guestCountryModel = Country::find($guestCountryParam);
            if ($guestCountryModel) {
                $guestCountryName = $guestCountryModel->name_en ?? $guestCountryModel->name;
            }
        } else {
            $guestCountryName = $guestCountryParam;
        }

        $tax = tax_amount($subtotal, $guestCountryName);

        $shipping_charge = delivery_charge($request->billing_area ?? $request->billing_city ?? $request->billing_state ?? $request->billing_country, ($request->billing_area) ? 'area' : null);
        $this->grand_total = $subtotal + $tax + $shipping_charge;

        // Generate unique sequential order number (numeric only)
        $order_number = $this->generateOrderNumber();

        if (Auth::check() && Session::has('Coupon_Id')) {
            $user_id = Auth::id();
            if (hasBlillingAddress($user_id) == 1) {
                $this->updateBillingAddress($request, $user_id);
            } else {
                $this->createBillingAddress($request, $user_id);
            }

            if (hasShippingAddress($user_id) == 1) {
                $this->updateShippingAddress($request, $user_id);
            } else {
                $this->createShippingAddress($request, $user_id);
            }
            $coupon = Coupon::whereId(Session::get('Coupon_Id'))->first();
            if ($coupon) {
                $orderCoupon = Order::where('Coupon_Id', $coupon->id)->where('User_Id', $user_id)->count();
                if ($orderCoupon != 0) {
                    session()->put('couponCode', null);
                    return redirect()->back()->with('error', 'Already used coupon Code');
                }
                $this->discount = $coupon->Amount;
            }
        }

        session()->put('order_number', $order_number);
        session()->put('shipping_charge', $shipping_charge);
        session()->put('subtotal', $subtotal);
        session()->put('discount', $this->discount);
        session()->put('grand_total', $this->grand_total);
        session()->put('tax', $tax);

        if ($request->payment == 'creditcard') {
            session()->put('payment_method_name', STRIPE);
            return $this->pay($this->grand_total, $this->discount, 'USD', 2, $request->payment_method);
        } elseif ($request->payment == 'sslcommerz') {
            $tran_id = uniqid();
            $post_data = array();
            $post_data['total_amount'] = $this->grand_total; # You cant not pay less than 10
            $post_data['currency'] = "BDT";
            $post_data['tran_id'] = $tran_id; // tran_id must be unique

            # CUSTOMER INFORMATION
            $post_data['cus_name'] = $request->billing_name;
            $post_data['cus_email'] = $request->billing_email;
            $post_data['cus_add1'] = $request->billing_street_address;
            $post_data['cus_add2'] = "";
            $post_data['cus_city'] = $request->billing_state;
            $post_data['cus_state'] = $request->billing_state;
            $post_data['cus_postcode'] = $request->billing_zipcode;
            $post_data['cus_country'] = $request->billing_country;
            $post_data['cus_phone'] = '8801XXXXXXXXX';
            $post_data['cus_fax'] = "";

            # SHIPMENT INFORMATION
            $post_data['ship_name'] = $request->shipping_name;
            $post_data['ship_add1'] = $request->shipping_street_address;
            $post_data['ship_add2'] = "";
            $post_data['ship_city'] = $request->shipping_state;
            $post_data['ship_state'] = $request->shipping_state;
            $post_data['ship_postcode'] = $request->shipping_zipcode;
            $post_data['ship_phone'] = "";
            $post_data['ship_country'] = $request->shipping_country;

            $post_data['shipping_method'] = "sslcommerz";
            $post_data['product_name'] = "Computer";
            $post_data['product_category'] = "Goods";
            $post_data['product_profile'] = "physical-goods";

            $this->orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $this->discount, $this->grand_total, SSLCOMMERZ, $post_data['tran_id'], null, false);

            $sslc = new SslCommerzNotification();
            $payment_options = $sslc->makePayment($post_data, 'hosted');
            if (!is_array($payment_options)) {
                print_r($payment_options);
                $payment_options = array();
            }
        } elseif ($request->payment == 'paypal') {
            session()->put('payment_method_name', PAYPAL);
            return $this->pay($this->grand_total, $this->discount, 'USD', 1, $request->payment_method);
        } elseif ($request->payment == 'COD') {
            return $this->orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $this->discount, $this->grand_total, COD);
        } elseif ($request->payment == 'bank') {
            if ($request->bank_transaction_number != null) {
                return $this->orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $this->discount, $this->grand_total, BANK_TRANSFER, $request->bank_transaction_number);
            } else {
                return redirect()->back()->with('error', 'Bank Transaction Number is Required.');
            }
        } elseif ($request->payment == 'razorpay') {
            $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
            $payment = $api->payment->fetch($request->razorpay_payment_id);
            try {
                $response = $api->payment->fetch($request->razorpay_payment_id)->capture(array('amount' => $payment['amount']));
                return $this->orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $this->discount, $this->grand_total, RAZORPAY, $request->razorpay_payment_id);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Something went wrong in razorpay.');
            }
        } else {
            return redirect()->back()->with('error', 'Payment Failed');
        }
    }

    public function pay($amount, $discount, $currency, $payment_platform, $payment_method)
    {
        $value = $amount - $discount;
        $paymentPlatform = $this->paymentPlatformResolver->resolveService($payment_platform);
        session()->put('paymentPlatformId', $payment_platform);
        return $paymentPlatform->handlePayment($value, $currency, $payment_method);
    }

    public function approval()
    {
        if (session()->has('paymentPlatformId')) {

            $paymentPlatform = $this->paymentPlatformResolver
                ->resolveService(session()->get('paymentPlatformId'));

            $payment = $paymentPlatform->handleApproval();

            if ($payment['success'] == true) {
                return $this->orderCreateCall(
                    session()->get('order_number'),
                    session()->get('shipping_charge'),
                    session()->get('tax'),
                    session()->get('subtotal'),
                    session()->get('discount'),
                    session()->get('grand_total'),
                    session()->get('payment_method_name')
                );
            }
            return redirect()->back()->with('error', $payment['message']);
        }
        return redirect()->back()->with('error', 'We can not retrieve payment platform. Please,  try again!');
    }

    public function cancelled()
    {
        return redirect()->back()->with('error', 'Payment cancelled!');
    }

    public function orderCreateCall($order_number, $shipping_charge, $tax, $subtotal, $discount, $grand_total, $payment_method, $txn = null, $buy_for = null, $shouldBroadcast = true)
    {
        $payment_status = $this->paymentStatus($payment_method);
        $order = $this->orederCreate($order_number, $shipping_charge, $tax, $subtotal, $discount, $grand_total, $payment_method, $payment_status, $txn, $buy_for, $shouldBroadcast);
        if ($order['success'] == true) {
            session()->forget('Coupon_Id');
            Cart::destroy();
            // Prepare modal data to show on home page
            $modal = [
                'line1' => __('تم استلام طلبك بنجاح! شكراً لاختيارك خيرات الأنعام'),
                'line2' => __('سيتم التواصل معك قريباً لتأكيد التوصيل.'),
                'order_number' => $order['data']->Order_Number ?? session('order_number') ?? null,
            ];
            return redirect()->route('front')->with(['order_success_modal' => $modal, 'success' => 'تم إنشاء الطلب بنجاح!']);
        }
        $modal = [
            'line1' => __('Ooops! Something went wrong.'),
            'line2' => __('Order not accepted'),
            'action' => route('checkout')
        ];
        return redirect()->route('checkout')->with('order_error_modal', $modal);
    }

    public function orderConfirmMail($order)
    {
        $ship = $order->shipping_address;
        $data['userName'] = $ship['name'] ?? null;
        $data['userEmail'] = $ship['email'] ?? null;
        $data['order'] = $order;
        $data['companyName'] = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
        $data['subject'] = __('Order Confirm Mail');
        $data['data'] = $order->Order_Number;
        $data['template'] = 'email.order-confirm';
        // dispatch(new OrderConfirmMail($data))->onQueue('email-send');
    }

    public function sendOrderNotification($id)
    {
        $order = Order::query()
            ->with('order_details', 'user', 'coupon', 'order_details.product', 'billing', 'shipping')
            ->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found']);
        }

        $pdfUrl = route('api.whatsapp.invoice_pdf', ['id' => $order->id, 'lang' => app()->getLocale()]);
        $phoneNumber = $order->billing_address['phone_number'] ?? $order->user->Number ?? '';
        $name = $order->billing_address['name'] ?? $order->user->name ?? 'Customer';

        try {
            $payload = [
                'phone_number' => str_starts_with($phoneNumber, '+') ? $phoneNumber : '+' . $phoneNumber,
                'name' => $name,
                'booking_id' => $order->id,
                'order_id' => $order->id,
                'pdf' => $pdfUrl,
            ];

            Log::info('WhatsApp Order Notification request (BYPASSED)', ['payload' => $payload]);

            // $response = Http::asForm()->post('https://whatsapi.hispeed.om/api/v1/whatsapp/success/payment', $payload);

            // Log::info('WhatsApp Order Notification response', [
            //     'order' => $order->Order_Number,
            //     'response' => $response->json(),
            //     'phone' => $phoneNumber,
            //     'pdf_url_sent' => $pdfUrl
            // ]);

            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            Log::error('Error sending WhatsApp order notification: ' . $ex->getMessage());
            return response()->json(['success' => false, 'message' => $ex->getMessage()]);
        }
    }

    public function sendPendingThawaniNotification($id, $paymentUrl)
    {
        $order = Order::query()
            ->with('order_details', 'user', 'coupon', 'order_details.product', 'billing', 'shipping')
            ->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found']);
        }

        $pdfUrl = route('api.whatsapp.invoice_pdf', ['id' => $order->id, 'lang' => app()->getLocale()]);
        $phoneNumber = $order->billing_address['phone_number'] ?? $order->user->Number ?? '';
        $name = $order->billing_address['name'] ?? $order->user->name ?? 'Customer';

        try {
            $payload = [
                'phone_number' => str_starts_with($phoneNumber, '+') ? $phoneNumber : '+' . $phoneNumber,
                'name' => $name,
                'booking_id' => $order->id,
                'order_id' => $order->id,
                'pdf' => $pdfUrl,
                'payment_url' => $paymentUrl
            ];

            Log::info('WhatsApp Pending Thawani Notification request (BYPASSED)', ['payload' => $payload]);

            // $response = Http::asForm()->post('https://whatsapi.hispeed.om/api/v1/whatsapp/pending/payment', $payload);

            // Log::info('WhatsApp Pending Thawani Notification response', [
            //     'order' => $order->Order_Number,
            //     'response' => $response->json(),
            //     'phone' => $phoneNumber,
            //     'pdf_url_sent' => $pdfUrl
            // ]);

            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            Log::error('Error sending WhatsApp pending Thawani notification: ' . $ex->getMessage());
            return response()->json(['success' => false, 'message' => $ex->getMessage()]);
        }
    }

    public function paymentStatus($payment_method)
    {
        // These methods are considered immediately paid
        if (in_array(strtoupper($payment_method), [STRIPE, PAYPAL, BANK_TRANSFER, 'STORE_PICKUP'])) {
            return PAYMENT_SUCCESS;
        }
        return PAYMENT_PENDING;
    }

    public function orederCreate($order_number, $shipping_charge, $tax, $subtotal, $discount, $grand_total, $payment_method, $payment_status, $txn = null, $buy_for = null, $shouldBroadcast = true)
    {
        try {
            $data = ['success' => false, 'data' => []];


            if (Auth::user() && Auth::user()->is_admin == 1) {
                $user_id = $buy_for;
                $admin_id = Auth::id();
            } else {
                $user_id = Auth::check() ? Auth::id() : null;
                $admin_id = null;
            }



            // Log computed amounts and addresses for debugging precision and delivery charge issues
            Log::info('Order create values', [
                'order_number' => $order_number,
                'user_id' => $user_id,
                'shipping_charge' => $shipping_charge,
                'tax' => $tax,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'grand_total' => $grand_total,
                'billing_address' => Session::get('billing_address'),
                'shipping_address' => Session::get('shipping_address'),
                'session_coupon_id' => Session::get('Coupon_Id'),
            ]);

            $initial_order_status = ORDER_PENDING;
            if (strtoupper($payment_method) == 'COD') {
                $initial_order_status = ORDER_PROCESSING;
            }

            $order = Order::create([
                'Order_Number' => $order_number,
                'User_Id' => $user_id, //Auth::check() ? Auth::id() : null,
                'admin_id' => $admin_id,
                'Billing_Id' => session('billing_id'),
                // 'Shipping_Id' => session('billing_id'),
                'billing_address' => Session::get('billing_address'),
                'shipping_address' => Session::get('shipping_address'),
                'Delivery_Charge' => $shipping_charge,
                'Tax' => $tax,
                'Sub_Total' => $subtotal,
                'Coupon_Id' => Session::get('Coupon_Id'),
                'Coupon_Amount' => $discount,
                'Grand_Total' => $grand_total,
                'Is_Free_Delivery' => false,
                'Is_Order_Successful' => false,
                'Is_Order_Completed' => false,
                'Payment_Method' => $payment_method,
                'Payment_Status' => $payment_status,
                'collection_method' => Session::get('collection_method'),
                'Order_Status' => $initial_order_status,
                'txn' => $txn != null ? $txn : randomString(8),
                'order_source' => 'web',
                'is_gift' => Session::get('is_gift', 0),
                'gift_recipient_name' => Session::get('gift_recipient_name'),
                'gift_recipient_phone' => Session::get('gift_recipient_phone'),
                'gift_message' => Session::get('gift_message'),
            ]);


            if ($order) {
                $couponId = Session::get('Coupon_Id');
                if ($couponId) {
                    $usedCoupon = \App\Models\Admin\Coupon::find($couponId);
                    if ($usedCoupon && $usedCoupon->usage_count > 0) {
                        $usedCoupon->decrement('usage_count');
                    }
                }
                
                session()->put('Coupon_Id', null);
                session()->put('couponCode', null);
                session()->put('CouponAmount', 0);
                session()->put('order_id', $order->id);
                session()->put('checkout_number', $order->Order_Number);
                $content = Cart::content();
                foreach ($content as $item) {
                    $this->subQtyProduct($item->id, $item->qty);
                    
                    $prodName = $item->name;
                    if (isset($item->options->print_name) && !empty($item->options->print_name)) {
                        $prodName .= ' [' . (__('Printed Name') ?: 'Printed Name') . ': ' . $item->options->print_name . ']';
                    }
                    if (isset($item->options->custom_box_details) && !empty($item->options->custom_box_details)) {
                        $prodName .= ' (' . $item->options->custom_box_details . ')';
                    }

                    OrderDetails::create([
                        'Order_Id' => $order->id,
                        'Product_Id' => is_numeric($item->id) ? $item->id : null,
                        'Product_Name' => $prodName,
                        'Image' => $item->options->image,
                        'Price' => $item->price,
                        'Color' => $item->options->color,
                        'Size' => $item->options->weight,
                        'Quantity' => $item->qty,
                        'Total_Price' => $item->price * $item->qty,
                    ]);

                    if (isset($item->options->is_custom_box) && $item->options->is_custom_box) {
                        \App\Models\CustomBoxOrder::create([
                            'order_id' => $order->id,
                            'template_name' => $item->options->template ?? 'Custom Box',
                            'capacity' => $item->options->capacity ?? 4,
                            'print_name' => $item->options->print_name ?? null,
                            'gift_message' => $item->options->gift_message ?? null,
                            'details' => $item->options->custom_box_details ?? '',
                            'price' => $item->price,
                            'prep_status' => 'pending',
                        ]);
                    }
                }

                if ($shouldBroadcast && $order->Order_Status == ORDER_PROCESSING) {
                    event(new \App\Events\OrderCreated($order));
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
                        \Illuminate\Support\Facades\Log::error('SmartLife sync failed in Web Checkout (orderCreate)', ['error' => $e->getMessage()]);
                    }
                }

                // Deduct wallet balance if used
                $wallet_used = session('wallet_used', 0);
                if ($wallet_used > 0 && Auth::check() && is_null($admin_id)) {
                    $user = Auth::user();
                    if ($user) {
                        $user->decrement('balance', $wallet_used);
                        \Illuminate\Support\Facades\Log::info("Wallet used for Order #{$order_number}: {$wallet_used} OMR");
                        // Update order with wallet usage
                        $order->wallet_used = $wallet_used;
                        $order->save();
                    }
                }

                $data['data'] = $order;
                $data['success'] = true;
            }
            // WhatsApp notification (Exclude pending Thawani orders, they have a special flow)
            if (!(strtoupper($payment_method) == 'THAWANI' && ($payment_status == PAYMENT_PENDING || $payment_status == 'PENDING'))) {
                $this->sendOrderNotification($order->id);
            }

            return $data;
        } catch (\Exception $e) {
            info('Order create exception: ' . $e->getMessage());
            Log::error('Order create exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function subQtyProduct($product_id, $qty)
    {
        $product = Product::find($product_id);

        if ($product) {
            $new_qty = $product->Quantity - $qty;
            $nn_qty = $new_qty < 1 ? 0 : $new_qty;
            
            $product->update([
                'Quantity' => $nn_qty,
            ]);
        }
    }

    public function redirectStripePage()
    {
        $data['currencies'] = Currency::all();
        $data['paymentPlatforms'] = PaymentPlatform::all();
        return view('front.pages.checkout.stripe', $data);
    }

    public function createBillingAddress($request, $user_id)
    {
        return Billing::create([
            'User_Id' => $user_id,
            'Name' => $request->billing_name,
            'Email' => $request->billing_email,
            'Street' => $request->billing_street_address,
            'State' => $request->billing_state,
            'City' => $request->billing_city,
            'Zipcode' => $request->billing_zipcode,
            'Country' => $request->billing_country,
        ]);
    }

    public function updateBillingAddress($request, $user_id)
    {
        $billing = Billing::where('User_Id', $user_id)->first();
        $billing->update([
            'Name' => $request->billing_name,
            'Email' => $request->billing_email,
            'Street' => $request->billing_street_address,
            'State' => $request->billing_state,
            'City' => $request->billing_city,
            'Zipcode' => $request->billing_zipcode,
            'Country' => $request->billing_country,
        ]);
        return $billing;
    }

    public function createShippingAddress($request, $user_id)
    {
        return Shipping::create([
            'User_Id' => $user_id,
            'Name' => $request->shipping_name,
            'Email' => $request->shipping_email,
            'Street' => $request->shipping_street_address,
            'State' => $request->shipping_state,
            'Zipcode' => $request->shipping_zipcode,
            'Country' => $request->shipping_country
        ]);
    }

    public function updateShippingAddress($request, $user_id)
    {
        $shipping = Shipping::where('User_Id', $user_id)->first();
        $shipping->update([
            'Name' => $request->shipping_name,
            'Email' => $request->shipping_email,
            'Street' => $request->shipping_street_address,
            'State' => $request->shipping_state,
            'Zipcode' => $request->shipping_zipcode,
            'Country' => $request->shipping_country
        ]);
        return $shipping;
    }

    public function generateRandomString($length = 20)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Generate a unique sequential numeric order number.
     * Format: 10000 + next order ID (e.g., 10077, 10078, ...)
     */
    protected function generateOrderNumber()
    {
        $maxId = Order::max('id') ?? 0;
        $nextNumber = 10000 + ($maxId + 1);
        $order_number = (string) $nextNumber;

        // Safety check: ensure uniqueness (should never loop, but just in case)
        while (Order::where('Order_Number', $order_number)->exists()) {
            $nextNumber++;
            $order_number = (string) $nextNumber;
        }

        return $order_number;
    }

    public function getTaxAmount(Request $request)
    {
        $data = [
            'success' => false,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'tax_show' => 0,
            'total_cost' => 0,
            'total_cost_curr' => 0,
            'delivery_charge' => 0,
            'delivery_charge_curr' => 0,
        ];
        // Determine country name to lookup tax by country (Tax.country stores country name)
        $countryParam = $request->country;
        $countryName = null;
        if (is_numeric($countryParam)) {
            $state = \App\Models\State::with('country')->find($countryParam);
            if ($state && $state->country) {
                $countryName = $state->country->name_en ?? $state->country->name;
            }
        } else {
            $countryName = $countryParam;
        }

        // If we couldn't resolve a country name, fall back to the raw param
        $countryForTax = $countryName ?: $countryParam;
        
        // Map "OM" (often passed by frontend) to "Oman" (database name)
        if ($countryForTax == 'OM') {
            $countryForTax = 'Oman';
        }

        $data['tax_amount'] = tax_amount(subtotal(), $countryForTax);
        $data['tax_show'] = currencyConverter($data['tax_amount']);

        // On state change we do not want to provide a city-level delivery charge.
        // Keep delivery charge at zero here so the client shows shipping=0 until a city is selected.
        $data['delivery_charge'] = 0;
        $data['delivery_charge_curr'] = currencyConverter(0);
        $subtotal = subtotal();
        $free_shipping_offer = Offer::where('type', 'free_shipping_with_total_bill')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->where('status', 1)
            ->where('minimum_total', '<=', $subtotal)
            ->first();

        if ($free_shipping_offer) {
            $data['delivery_charge'] = 0;
            $data['delivery_charge_curr'] = currencyConverter(0);
        }

        // IMPORTANT: Since we force delivery_charge to be 0 for display above (lines 1404),
        // we MUST also use 0 for the Total calculation here.
        // The actual delivery charge will be added later when the user selects a City/Area.
        $deliveryCharge = 0;

        $taxAmount = $data['tax_amount'];
        $couponAmount = Session::get('CouponAmount');
        $currentSubtotal = subtotal();

        $data['total_cost'] = $currentSubtotal + $deliveryCharge + $taxAmount - $couponAmount;



        $wallet_used = 0;
        if (auth()->check() && !auth()->user()->is_admin) {
            $balance = auth()->user()->balance;
            if ($balance > 0) {
                if ($balance >= $data['total_cost']) {
                    $wallet_used = $data['total_cost'];
                    $data['total_cost'] = 0;
                } else {
                    $wallet_used = 0;
                    // $data['total_cost'] remains full
                }
            }
        }
        $data['wallet_used'] = currencyConverter($wallet_used);
        $data['total_cost_curr'] = currencyConverter($data['total_cost']);
        $data['success'] = true;
        return $data;
    }

    public function orderTrack(Request $request)
    {
        $order = Order::where('Order_Number', $request->order_number)->with('order_details', 'order_details.product')->first();
        if (is_null($order)) {
            return redirect()->back()->with('error', __('No order found!'));
        }
        $data['order'] = Order::where('Order_Number', $request->order_number)->with('order_details', 'order_details.product')->first();
        $data['title'] = __('Order Track');
        $data['description'] = __('Order Track');
        $data['keywords'] = __('Order Track');
        return view('front.pages.checkout.order-track', $data);
    }

    public function paymentSuccess(Request $request)
    {
        $data = $request->all();
        $order = Order::where('Order_Number', $data['order_number'])->first();
        if (!$order) {
            \Illuminate\Support\Facades\Log::error('paymentSuccess: Order not found.', ['order_number' => $data['order_number'] ?? 'N/A']);
            return redirect()->route('front')->with('error', __('Order not found.'));
        }

        $order->Is_Order_Successful = true;
        $order->Is_Order_Completed = true;
        $order->Payment_Method = THAWANI;
        $order->Payment_Status = PAYMENT_SUCCESS;
        $order->Order_Status = ORDER_PROCESSING;
        $order->is_paid = 1;

        $order->save();
        event(new \App\Events\OrderCreated($order));
        
        // Two-Step Sync: Update the existing SmartLife invoice to "Paid"
        if (config('smartlife.sync_enabled')) {
            try {
                Log::info('SmartLife Sync attempt via paymentSuccess (Confirming Paid)', [
                    'order' => $order->Order_Number,
                    'is_paid' => $order->is_paid,
                    'payment_status' => $order->Payment_Status,
                    'existing_erp_id' => $order->smartlife_invoice_id
                ]);

                $smartLifeService = new \App\Services\SmartLifeErpService();
                $invoiceId = $smartLifeService->submitOrder($order);

                if ($invoiceId && !$order->smartlife_invoice_id) {
                    $order->smartlife_synced_at = now();
                    $order->smartlife_invoice_id = $invoiceId;
                    $order->save();
                    Log::info('SmartLife Sync via paymentSuccess: New ERP ID assigned', ['order' => $order->Order_Number, 'erp_id' => $invoiceId]);
                } else {
                    Log::info('SmartLife Sync via paymentSuccess completed', ['order' => $order->Order_Number, 'erp_id' => $invoiceId]);
                }
            } catch (\Exception $e) {
                Log::error('SmartLife update sync failed in paymentSuccess', [
                    'order' => $order->Order_Number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Clear cart so it doesn't stay at the top of the site
        session()->forget('cart');
        session()->forget('coupon');
        session()->forget('wallet_used');

        try {
            $this->sendOrderNotification($order->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('paymentSuccess: Notification failure (ignoring).', ['error' => $e->getMessage()]);
        }
        // Show success modal on homepage
        $modal = [
            'line1' => __('THANK YOU FOR CHOOSING Hi Speed'),
            'line2' => __('Orders arriving at 7 PM will be delivered the following day.'),
            'order_number' => $order->Order_Number ?? null,
        ];
        return redirect()->route('front')->with(['order_success_modal' => $modal, 'success' => 'Order successfully created!']);
    }

    public function paymentCancel(Request $request)
    {
        $data = $request->all();
        $order = Order::where('Order_Number', $data['order_number'])->first();

        $order->Is_Order_Successful = false;
        $order->Is_Order_Completed = false;
        $order->Order_Status = ORDER_CANCELLED;

        $order->save();

        return redirect()->route('front')->with('error', 'Order Cancelled!');
    }
}
