<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Mail\OrderConfirmMail;
use App\Models\Admin\Addition;
use App\Models\Admin\Billing;
use App\Models\Admin\Coupon;
use App\Models\Admin\Order;
use App\Models\Admin\OrderDetails;
use App\Models\Admin\Product;
use App\Models\Admin\Shipping;
use App\Models\City;
use App\Models\DeliveryCharge;
use App\Models\PaymentModel;
use App\Models\State;
use App\Models\Tax;
use App\Models\WeightProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // Free weight allowance in kilograms before extra weight fees apply
    const FREE_WEIGHT_LIMIT_KG = 25;
    public function checkoutOrder(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();
        $user_id = $user->id;
        Log::info('Checkout requested', ['request' => $validated]);

        //        try {
        // Simulate cart items and calculate totals
        $subtotal = $this->calculateSubtotal($validated['cart_items']);
        // Prefer per-country Tax table (admin) if available, otherwise fallback to global tax percentage
        $country = $validated['billing_country'] ?? null;
        $tax = tax_amount($subtotal, $country);
        $collection_method = $request->Collection_Method ?? 'Delivery';
        $isPickup = in_array(strtolower($collection_method), ['store_pickup', 'store_pickup']);
        
        $shipping_charge = $isPickup ? 0 : delivery_charge($validated['billing_city'] ?? $validated['billing_state'] ?? $validated['billing_country']);
        $weight_charge = $isPickup ? 0 : $this->calculateExtraWeightFees($validated['cart_items']);
        $grandTotal = $subtotal + $shipping_charge + $weight_charge + $tax;

        // Apply coupon discount if available
        $discount = 0;
        if (isset($validated['coupon_code'])) {
            $coupon = Coupon::where('CouponCode', $validated['coupon_code'])
                ->where('Status', 1)
                ->where('ExpireDate', '>=', Carbon::now()->toDateString())
                ->where('Min_Expenses', '<=', $subtotal)
                ->first();

            if ($coupon && !$this->hasCouponBeenUsed($coupon->id, $user_id)) {
                $discount = $coupon->amount;
                $grandTotal -= $discount;
            } else {
                return response()->json(['error' => 'Invalid or already used coupon code'], 400);
            }
        }

        // Generate unique order number
        $order_number = $this->generateOrderNumber();

        // Address handling
        if ($user_id) {
            if (hasBlillingAddress($user_id)) {
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
                'zipcode' => $billing_create->Zipcode,
                'country' => $billing_create->Country,
            ];

            $shipping_address = $billing_address;

        }

        // Update city and state names
        $city = City::find($validated['billing_city'] ?? '');
        $state = State::find($validated['billing_state']);
        $billing_address['state_en'] = $state->name_en ?? '';
        $billing_address['state_ar'] = $state->name_ar ?? '';

        $billing_address['city_en'] = $city->name_en ?? '';
        $billing_address['city_ar'] = $city->name_ar ?? '';
        $billing_address['phone_number'] = $user->Number ?? '';

        $shipping_address['phone_number'] = $user->Number ?? '';

        $payment_method = $validated['Payment_Method'] ?? '';
        if ($payment_method == 'CashOnDelivery') {
            $payment_method = 'COD';
        }

        $initial_status = ORDER_PENDING;
        if (strtoupper($payment_method) == 'COD' || $isPickup) {
            $initial_status = ORDER_PROCESSING;
        }


        // Create order
        $order = Order::create([
            'Order_Number' => $order_number,
            'User_Id' => Auth::id(),
            'Billing_Id' => $billing_create->id,
            'billing_address' => $billing_address,
            'shipping_address' => $shipping_address,
            'Delivery_Charge' => $shipping_charge,
            'Tax' => $tax,
            'Sub_Total' => $subtotal,
            'Coupon_Id' => $validated['coupon_code'] ?? null,
            'Coupon_Amount' => $discount,
            'Grand_Total' => $grandTotal,
            'Is_Free_Delivery' => false,
            'Is_Order_Completed' => false,
            'Payment_Method' => $payment_method,
            'collection_method' => strtolower($collection_method),
            'Order_Status' => $initial_status,
            'order_source' => 'app',
        ]);
        if ($order) {
            foreach ($validated['cart_items'] as $item) {
                // Decrement the product quantity in stock
                $this->subQtyProduct($item['product_id'], $item['quantity']);
                $product = Product::find($item['product_id']);
                $sizePrice = 0;
                $sizeWeight = 0;

                // If size_id is provided, calculate the size price and weight
                if (!empty($item['size_id'])) {
                    $size = $product->sizes()->where('size_product.Size_Id', $item['size_id'])->first();
                    $sizePrice = $size->pivot->price ?? 0;
                    $sizeWeight = $size->pivot->weight ?? 0;
                }

                // Calculate the weight price and weight value
                $weight = WeightProduct::where('id', $item['weight_id'])->first();
                $weightPrice = $weight->price ?? 0;
                $weightValue = $weight->weight ?? 0;

                // Calculate the total price for the item
                $basePrice = $sizePrice + $weightPrice;
                if ($basePrice == 0) {
                    $basePrice = $product->Price ?? 0;
                }
                $price = $basePrice;

                // Calculate addition prices
                $additions = Addition::whereIn('id', $item['addition_ids'] ?? [])->get();
                $additionPrice = $additions->sum('price');
                $price += $additionPrice;

                // Apply discount to the price if available
                if ($product->Discount) {
                    $discountAmount = ($product->Discount / 100) * $price;
                    $price -= $discountAmount;
                }
                $productName = App::getLocale() === 'ar' ? $product->fr_Product_Name : $product->en_Product_Name;

                // Create order details
                OrderDetails::create([
                    'Order_Id' => $order->id,
                    'Product_Id' => $item['product_id'],
                    'Product_Name' => $productName,
                    'Image' => $product->Primary_Image,
                    'Price' => $price,
                    //                    'Color' => $item['color'] ?? null,
                    'Size' => $sizeWeight,
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
                    \Illuminate\Support\Facades\Log::error('SmartLife sync failed in API checkout', ['error' => $e->getMessage()]);
                }
            }
        }

        // Trigger OneSignal/Delivery Notification and return for COD orders
        if (strtoupper($payment_method) == 'COD') {
            event(new \App\Events\OrderCreated($order));
            $this->sendOrderNotification($order->id);
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully (COD)',
                'order_number' => $order_number
            ]);
        }

        $phoneNumber = auth()->user()->Number;
        // Initialize payment data for Thawani
        $paymentData = [
            'client_reference_id' => $order_number,
            'mode' => 'payment',
            'products' => [],
            'success_url' => route('api.thawani.success', ['order_number' => $order_number, 'phone_number' => $phoneNumber]),
            'cancel_url' => route('api.thawani.fail', ['order_number' => $order_number]),
            'metadata' => [
                'order_number' => $order_number,
                'shipping_charge' => $shipping_charge,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'tax' => $tax,
            ]
        ];

        // Add tax to payment data if applicable
        if ($tax) {
            $paymentData['products'][] = [
                'name' => 'tax',
                'quantity' => 1,
                'unit_amount' => round($tax * 1000, 2),
            ];
        }

        // Add weight charge to payment data if applicable
        if ($weight_charge) {
            $paymentData['products'][] = [
                'name' => 'weight extra charge',
                'quantity' => 1,
                'unit_amount' => round($weight_charge * 1000, 2),
            ];
        }

        // Add shipping charge to payment data if applicable
        if ($shipping_charge) {
            $paymentData['products'][] = [
                'name' => 'shipping charge',
                'quantity' => 1,
                'unit_amount' => round($shipping_charge * 1000, 2),
            ];
        }

        // Process each cart item
        foreach ($validated['cart_items'] as $item) {
            $product = Product::find($item['product_id']);
            $sizePrice = 0;
            $sizeWeight = 0;

            // If size_id is provided, calculate the size price and weight
            if (!empty($item['size_id'])) {
                $size = $product->sizes()->where('size_product.Size_Id', $item['size_id'])->first();
                $sizePrice = $size->pivot->price ?? 0;
                $sizeWeight = $size->pivot->weight ?? 0;
            }

            // Calculate the weight price and weight value
            $weight = WeightProduct::where('id', $item['weight_id'])->first();
            $weightPrice = $weight->price ?? 0;
            $weightValue = $weight->weight ?? 0;

            // Calculate the total price for the item
            $price = $sizePrice + $weightPrice;

            // Calculate addition prices
            $additions = Addition::whereIn('id', $item['addition_ids'] ?? [])->get();
            $additionPrice = $additions->sum('price');
            $price += $additionPrice;

            // Apply discount to the price if available
            if ($product->Discount) {
                $discountAmount = ($product->Discount / 100) * $price;
                $price -= $discountAmount;
            }

            // Add product details to payment data
            $productName = App::getLocale() === 'ar' ? $product->fr_Product_Name : $product->en_Product_Name;
            $cleanName = preg_replace('/[^A-Za-z0-9\s\x{0600}-\x{06FF}]/u', '', $productName);
            $paymentData['products'][] = [
                'name' => Str::limit($cleanName, 35),
                'quantity' => $item['quantity'],
                'unit_amount' => round($price * 1000, 2),
            ];
        }


        // Make the API call to Thawani
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'thawani-api-key' => config('services.thawani.secret_key')
        ])->post(config('services.thawani.checkout_url') . '/checkout/session', $paymentData);

        Log::info('Thawani API session response', ['response' => $response->body()]);

        if ($response->successful()) {
            $sessionId = $response['data']['session_id'] ?? '';
            $order->update(['session_id' => $sessionId]);
            $payment = PaymentModel::create([
                'session_id' => $sessionId,
                'user_id' => $user_id,
                'order_number' => $order_number,
                'amount' => $grandTotal,
                'status' => 'CREATED',
            ]);


            // Redirect the user to the Thawani payment page
            $paymentUrl = config('services.thawani.pay_url') . $sessionId . "?key=" . config('services.thawani.public_key');
            info("payment url", ['url' => $paymentUrl]);
            return response()->json(['url' => $paymentUrl]);
        } else {
            return response()->json(['error' => 'Failed to create payment session'], 500);
        }

        //        } catch (\Exception $e) {
//            Log::error('Error during checkout', ['error' => $e->getMessage()]);
//            return response()->json(['error' => 'Something went wrong'], 500);
//        }
    }

    protected function calculateSubtotal(array $cartItems)
    {
        $subtotal = 0;

        foreach ($cartItems as $item) {
            // Retrieve the product and related data
            $product = Product::find($item['product_id']);
            $sizePrice = 0;

            // If size_id is provided, calculate the size price
            if (!empty($item['size_id'])) {
                $size = $product->sizes()->where('size_product.Size_Id', $item['size_id'])->first();
                $sizePrice = $size->pivot->price ?? 0;
            }

            // Calculate the weight price
            $weight = WeightProduct::find($item['weight_id']);
            $weightPrice = $weight->price ?? 0;

            // Calculate the addition prices
            $additions = Addition::whereIn('id', $item['addition_ids'] ?? [])->get();
            $additionPrice = $additions->sum('price');

            // Calculate the total price for the item
            $basePrice = $sizePrice + $weightPrice;
            if ($basePrice == 0) {
                $basePrice = $product->Price ?? 0;
            }
            $price = $basePrice + $additionPrice;

            // Apply discount if available
            if ($product->Discount) {
                $discountAmount = ($product->Discount / 100) * $price;
                $price -= $discountAmount;
            }

            // Add to subtotal considering the quantity
            $subtotal += $price * $item['quantity'];
        }

        return $subtotal;
    }


    protected function calculateExtraWeightFees(array $cartItems)
    {
        $totalWeightGrams = 0;

        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            $sizeWeight = 0;

            // If size_id is provided, calculate the size weight
            if (!empty($item['size_id'])) {
                $size = $product->sizes()->where('size_product.Size_Id', $item['size_id'])->first();
                $sizeWeight = $size->pivot->weight ?? 0;
            }

            // Calculate the weight of the selected weight product
            $weight = WeightProduct::find($item['weight_id']);
            $weightValue = $weight->weight ?? 0;


            // Calculate the total weight for the item
            $itemWeight = $sizeWeight + $weightValue;
            $totalWeightGrams += $itemWeight * $item['quantity'];
        }

        // Convert grams to kilograms
        $totalWeightKg = $totalWeightGrams / 1000;
        $shippingFee = 0;

        if ($totalWeightKg > self::FREE_WEIGHT_LIMIT_KG) {
            $extraKg = ceil($totalWeightKg - self::FREE_WEIGHT_LIMIT_KG);
            $shippingFee = $extraKg * 0.100;
        }

        return $shippingFee;
    }

    protected function calculateTax($subtotal, $country = null)
    {
        $tax = 0;
        if ($country) {
            $tax_percentage = Tax::where('country', $country)->where('status', 'active')->first();
            if ($tax_percentage) {
                $tax = ($subtotal * $tax_percentage->percentage) / 100;
            }
        }
        return $tax;
    }

    protected function calculateShippingCharge($city = null)
    {
        $shipping_charge = 0;
        if ($city) {
            $delivery_charge = DeliveryCharge::where('city_id', $city)->where('status', 'active')->first();
            if ($delivery_charge) {
                $shipping_charge = $delivery_charge->charge;
            }
        }
        return $shipping_charge;
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

    protected function hasCouponBeenUsed($coupon_id, $user_id)
    {
        return Order::where('Coupon_Id', $coupon_id)->where('User_Id', $user_id)->exists();
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

    public function sendOrderNotification($id)
    {
        $order = Order::query()
            ->with('order_details', 'user', 'coupon', 'order_details.product', 'billing', 'shipping')
            ->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found']);
        }

        $pdfUrl = route('api.whatsapp.invoice_pdf', ['id' => $order->id]);
        $phoneNumber = $order->billing_address['phone_number'] ?? $order->user->Number ?? '';

        try {
            // Trigger WhatsApp notification for all successful orders (Online/COD)
            // Updated: Using order_id (DB primary key) instead of booking_id, removed pdf parameter
            $name = $order->billing_address['name'] ?? $order->user->name ?? 'Customer';
            $response = Http::asForm()->post('https://whatsapi.hispeed.om/api/v1/whatsapp/success/payment', [
                'phone_number' => $phoneNumber,
                'name' => $name,
                'order_id' => $order->id,
            ]);

            Log::info('WhatsApp Order Notification response (API)', [
                'order' => $order->Order_Number,
                'response' => $response->json(),
                'phone' => $phoneNumber
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            Log::error('Error sending WhatsApp order notification in API: ' . $ex->getMessage());
            return response()->json(['success' => false, 'message' => $ex->getMessage()]);
        }
    }

    public function success(Request $request)
    {
        info("inside success checkout controller");
        info($request->all());
        $orderNumber = $request->get('order_number');
        $phoneNumber = $request->get('phone_number');

        $order = Order::where('Order_Number', $orderNumber)->first();
        if (!$order) {
            return redirect()->to(url('/'));
        }

        $order->update([
            'is_paid' => 1,
            'Is_Order_Successful' => true,
            'Is_Order_Completed' => true,
            'Payment_Method' => THAWANI,
            'Payment_Status' => PAYMENT_SUCCESS,
            'Order_Status' => ORDER_PROCESSING
        ]);

        // Two-Step Sync: Update the existing SmartLife invoice to "Paid"
        if (config('smartlife.sync_enabled')) {
            try {
                $smartLifeService = new \App\Services\SmartLifeErpService();
                $invoiceId = $smartLifeService->submitOrder($order);
                if ($invoiceId && !$order->smartlife_invoice_id) {
                    $order->smartlife_synced_at = now();
                    $order->smartlife_invoice_id = $invoiceId;
                    $order->save();
                }
                Log::info('SmartLife Sync via API success (Updated to Paid)', ['order' => $order->Order_Number, 'erp_id' => $invoiceId]);
            } catch (\Exception $e) {
                Log::error('SmartLife update sync failed in API success', ['error' => $e->getMessage()]);
            }
        }
        event(new \App\Events\OrderCreated($order));

        try {
            $this->sendOrderNotification($order->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('API Checkout: Notification failure (ignoring).', ['error' => $e->getMessage()]);
        }

        return redirect()->to(url('/'));
        //        return redirect()->to("/#/donations/paymentstatus/?payId={$order->Order_Number}");
//        return redirect()->to($request->getHost());
        //        return redirect()->to($request->getHost() . "/services/paymentstatus/?payId={$order->Id}");
    }

    public function fail(Request $request)
    {
        $orderNumber = $request->get('order_number');
        $order = Order::where('Order_Number', $orderNumber)->first();
        Log::info('Payment failed', ['order_id' => $orderNumber]);
        if (!$order)
            return redirect()->to(url('/'));

        $order->update([
            'order_status' => $response['data']['payment_status'] ?? $order->order_status,
            'Is_Order_Successful' => false,
            'Is_Order_Completed' => false,
            'Payment_Status' => PAYMENT_SUCCESS,
            'Order_Status' => ORDER_CANCELLED
        ]);
        Log::info('Order status updated on failure', ['order_id' => $order->Id]);
        return redirect()->to(url('/'));
        //        return redirect()->to($request->getHost()."/services/paymentstatus/?payId={$order->Id}");

        //        return redirect()->to('https://zakat-website.netlify.app/aboutus/');

    }

}
