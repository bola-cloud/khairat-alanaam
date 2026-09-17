<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Controller;
use App\Jobs\OrderConfirmMail;
use App\Models\Admin\Order;
use App\Models\Admin\OrderDetails;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class OrderController extends Controller
{

    protected $paymentController;

    public function __construct(PaymentController $paymentController)
    {
        $this->paymentController = $paymentController;
    }

    public function create()
    {
        $users = \App\Models\User::orderBy('id', 'desc')->get();
        $products = \App\Models\Admin\Product::orderBy('id', 'desc')->get();
        $states = \App\Models\State::all();
        return view('admin.pages.orders.create', compact('users', 'products', 'states'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'guest_name' => 'required_if:user_id,guest',
            'guest_phone' => 'required_if:user_id,guest',
            'state_id' => 'required_unless:collection_method,store_pickup|nullable|exists:states,id',
            'city_id' => 'required_unless:collection_method,store_pickup|nullable|exists:cities,id',
            'area_id' => 'required_unless:collection_method,store_pickup|nullable|exists:areas,id',
            'street_address' => 'required_unless:collection_method,store_pickup|nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'shipping_charge' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $userId = $request->user_id === 'guest' ? null : $request->user_id;
        
        // Generate Order Number (Sequential 10000+ pattern as in Checkout)
        $maxId = Order::max('id') ?? 0;
        $nextNumber = 10000 + ($maxId + 1);
        $order_number = (string) $nextNumber;
        while (Order::where('Order_Number', $order_number)->exists()) {
            $nextNumber++;
            $order_number = (string) $nextNumber;
        }

        $subtotal = 0;
        foreach ($request->products as $item) {
            $product = \App\Models\Admin\Product::find($item['id']);
            if (isset($item['price']) && is_numeric($item['price'])) {
                $price = floatval($item['price']);
            } else {
                $price = $product->Price;
                if ($product->Discount) {
                    $price -= ($product->Discount / 100) * $price;
                }
            }
            $subtotal += $price * $item['quantity'];
        }

        $shipping_charge = floatval($request->shipping_charge ?? 0);
        $discount = floatval($request->discount ?? 0);
        $grand_total = $subtotal + $shipping_charge - $discount;

        // Resolve Address Names
        $state = \App\Models\State::find($request->state_id);
        $city = \App\Models\City::find($request->city_id);
        $area = \App\Models\Area::find($request->area_id);

        $customerName = $request->guest_name;
        $customerPhone = str_replace('+', '', $request->guest_phone);
        
        // Normalize phone number: add 968 if it's 8 digits and missing the prefix
        if ($customerPhone && !str_starts_with($customerPhone, '968') && strlen($customerPhone) == 8) {
            $customerPhone = '968' . $customerPhone;
        }
        $customerEmail = $request->guest_email;

        if ($userId) {
            $user = \App\Models\User::find($userId);
            $customerName = $user->name;
            $customerPhone = $user->Number;
            $customerEmail = $user->email;
        }

        $order = new Order();
        $order->Order_Number = $order_number;
        $order->User_Id = $userId;
        $order->admin_id = auth()->id();
        
        $billing = [
            'name' => $customerName,
            'email' => $customerEmail,
            'phone_number' => $customerPhone,
            'street' => $request->street_address,
            'state' => $request->state_id,
            'state_en' => $state->name_en ?? '',
            'state_ar' => $state->name_fr ?? '',
            'city' => $request->city_id,
            'city_en' => $city->name_en ?? '',
            'city_ar' => $city->name_fr ?? '',
            'area' => $request->area_id,
            'area_en' => $area->name_en ?? '',
            'area_ar' => $area->name_fr ?? '',
            'zipcode' => '',
            'country' => 'Oman',
        ];

        if ($userId) {
            \App\Models\Admin\Billing::updateOrCreate(['User_Id' => $userId], [
                'Name' => $customerName,
                'Email' => $customerEmail,
                'Street' => $request->street_address,
                'State' => $request->state_id,
                'City' => $request->city_id,
            ]);
        }
        
        $order->billing_address = $billing;
        $order->shipping_address = $billing;
        $order->Sub_Total = $subtotal;
        $order->Delivery_Charge = $shipping_charge;
        $order->Coupon_Amount = $discount;
        $order->Tax = 0;
        $order->Grand_Total = $grand_total;
        $order->Payment_Method = $request->payment_method;
        
        $collection_method = $request->collection_method ?? 'delivery';
        
        // Payment Status logic:
        // - COD / THAWANI: pending (unpaid initially)
        // - BANK_TRANSFER / OTHER: paid (admin confirms transfer before creating order)
        if ($request->payment_method === 'COD' || $request->payment_method === 'thawani') {
            $order->Payment_Status = PAYMENT_PENDING;
            $order->is_paid = 0;
        } else {
            // BANK_TRANSFER or any other paid method
            $order->Payment_Status = PAYMENT_SUCCESS;
            $order->is_paid = 1;
        }
        
        $order->collection_method = strtolower($collection_method);
        $order->order_source = 'admin';
        
        if (strtoupper($collection_method) === 'STORE_PICKUP') {
            // No delivery charge for in-store pickup
            $order->Delivery_Charge = 0;
            $grand_total = $subtotal - $discount;
            $order->Grand_Total = $grand_total;
        }
        
        // Admin created orders should always start as ORDER_PROCESSING (قيد المعالجة)
        // to ensure they appear in the mobile application immediately.
        $order->Order_Status = ORDER_PROCESSING;
        $order->order_source = 'admin';
        $order->txn = 'ADMIN-'.time();
        $order->save();

        foreach ($request->products as $item) {
            $product = \App\Models\Admin\Product::find($item['id']);
            if (isset($item['price']) && is_numeric($item['price'])) {
                $price = floatval($item['price']);
            } else {
                $price = $product->Price;
                if ($product->Discount) {
                    $price -= ($product->Discount / 100) * $price;
                }
            }
            OrderDetails::create([
                'Order_Id' => $order->id,
                'Product_Id' => $product->id,
                'Product_Name' => $product->en_Product_Name,
                'Image' => $product->Primary_Image,
                'Price' => $price,
                'Quantity' => $item['quantity'],
                'Total_Price' => $price * $item['quantity'],
            ]);

            // Deduct Stock (Same logic as Checkout)
            $this->subQtyProduct($product->id, $item['quantity']);
        }

        // Dispatch OrderCreated Event (Triggers notifications, etc.)
        try {
            event(new \App\Events\OrderCreated($order));
        } catch (\Exception $e) {
            \Log::error('Admin OrderCreated Event Error: ' . $e->getMessage());
        }

        // Handle Thawani payment session creation and WhatsApp link
        if ($request->payment_method === 'thawani') {
            try {
                $checkoutProduct = [];
                $discountAmount = $discount;
                foreach ($request->products as $item) {
                    $product = \App\Models\Admin\Product::find($item['id']);
                    if (isset($item['price']) && is_numeric($item['price'])) {
                        $price = floatval($item['price']);
                    } else {
                        $price = $product->Price;
                        if ($product->Discount) {
                            $price -= ($product->Discount / 100) * $price;
                        }
                    }
                    
                    if ($subtotal > 0) {
                        $itemTotalPrice = $price * $item['quantity'];
                        $itemDiscount = ($itemTotalPrice / $subtotal) * $discountAmount;
                        $newUnitAmount = $price - ($itemDiscount / $item['quantity']);
                    } else {
                        $newUnitAmount = $price;
                    }

                    $cleanName = preg_replace('/[^A-Za-z0-9\s\x{0600}-\x{06FF}]/u', '', $product->en_Product_Name);
                    $checkoutProduct[] = [
                        'name' => \Illuminate\Support\Str::limit($cleanName, 35),
                        'quantity' => $item['quantity'],
                        'unit_amount' => number_format($newUnitAmount, 3) * 1000,
                    ];
                }
                if ($shipping_charge > 0) {
                    $checkoutProduct[] = [
                        'name' => 'Shipping Charge',
                        'quantity' => 1,
                        'unit_amount' => number_format($shipping_charge, 3) * 1000,
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
                    'success_url' => route('thawani.success', ['order_number' => $order_number]),
                    'cancel_url' => route('thawani.cancel', ['order_number' => $order_number]),
                    'metadata' => [
                        'order_number' => $order_number,
                        'shipping_charge' => $shipping_charge,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'grand_total' => $grand_total,
                        'tax' => 0,
                    ]
                ]);

                if ($response->successful()) {
                    $paymentJsonData = $response->json();
                    
                    $payment = [
                        'session_id' => $paymentJsonData['data']['session_id'],
                        'user_id' => $userId,
                        'admin_id' => auth()->id(),
                        'order_number' => $order_number,
                        'amount' => $grand_total,
                        'status' => 'CREATED',
                    ];
                    $paymentRequest = new \Illuminate\Http\Request($payment);
                    $this->paymentController->createPayment($paymentRequest);

                    $paymentUrl = config('services.thawani.pay_url') . $paymentJsonData['data']['session_id'] . '?key=' . config('services.thawani.public_key');
                    
                    // Send WhatsApp message via new endpoint for Admin created orders
                    try {
                        $pdfUrl = url("api/whatsapp/order-invoice/{$order->id}/invoice.pdf?lang=" . (app()->getLocale() ?? 'ar'));
                        $phoneNumber = str_starts_with($customerPhone, '+') ? $customerPhone : '+' . $customerPhone;
                        
                        $payload = [
                            'phone_number' => $phoneNumber,
                            'pdf' => $pdfUrl,
                            'payment_url' => $paymentUrl
                        ];
                        
                        \Log::info('Admin WhatsApp Thawani Notification Request', ['payload' => $payload]);
                        
                        $whatsappResponse = Http::asForm()->post('https://whatsapi.hispeed.om/api/v1/whatsapp/payment_with_pdf', $payload);
                        
                        \Log::info('Admin WhatsApp Thawani Notification Response', [
                            'order' => $order->Order_Number,
                            'response' => $whatsappResponse->json(),
                            'phone' => $phoneNumber
                        ]);
                    } catch (\Exception $ex) {
                        \Log::error('Admin WhatsApp Thawani Notification Error: ' . $ex->getMessage());
                    }
                } else {
                    \Log::error('Thawani Session Creation Failed for Admin Order', ['response' => $response->body()]);
                }
            } catch (\Exception $e) {
                \Log::error('Thawani Admin Order Exception: ' . $e->getMessage());
            }
        }

        // Sync with SmartLife ERP
        if (config('smartlife.sync_enabled')) {
            try {
                app(\App\Services\SmartLifeErpService::class)->submitOrder($order);
            } catch (\Exception $e) {
                \Log::error('Admin SmartLife Sync Error: ' . $e->getMessage());
            }
        }

        // Send WhatsApp Notification (Exclude pending Thawani orders, they have a special flow)
        if (strtolower($request->payment_method) !== 'thawani') {
            try {
                app(\App\Http\Controllers\Frontend\CheckoutController::class)->sendOrderNotification($order->id);
            } catch (\Exception $e) {
                \Log::error('Admin Order WhatsApp Notification failed', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('admin.orders', 'all')->with('success', __('Order created successfully and WhatsApp notification sent.'));
    }

    /**
     * Replicated from CheckoutController to ensure stock consistency.
     */
    private function subQtyProduct($product_id, $qty)
    {
        $product = \App\Models\Admin\Product::find($product_id);
        if (!$product) return;

        $new_qty = max(0, $product->Quantity - $qty);
        $product->update(['Quantity' => $new_qty]);
    }
    public function orders(Request $request, $status)
    {
        if ($request->ajax()) {
            if ($status == 'pending') {
                $data = Order::where('Order_Status', ORDER_PENDING);
            } elseif ($status == 'processing') {
                $data = Order::where('Order_Status', ORDER_PROCESSING);
            } elseif ($status == 'shipped') {
                $data = Order::where('Order_Status', ORDER_SHIPPED);
            } elseif ($status == 'delivered') {
                $data = Order::where('Order_Status', ORDER_DELIVERED);
            } elseif ($status == 'returned') {
                $data = Order::where('Order_Status', ORDER_RETURN);
            } elseif ($status == 'cancelled') {
                $data = Order::where('Order_Status', ORDER_CANCELLED);
            } elseif ($status == 'all') {
                $data = Order::query();
            }
            $data = $data->orderBy('id', 'desc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) use ($status) {
                    $btn = '<div class="action__buttons" style="display: flex; gap: 8px;">';
                    // $btn = $btn . '<a href="javascript:void(0)" class="btn-action" data-bs-toggle="modal" data-bs-target="#invoiceModal' . $data->id . '" title="' . __('Invoice') . '"><i class="fas fa-file-invoice"></i></a>';
                    $btn = $btn . '<a href="javascript:void(0)" class="btn-action" onclick="orderDetails(' . $data->id . ')" title="' . __('Invoice') . '" style="padding: 6px 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="fas fa-file-invoice"></i></a>';
                    $btn = $btn . '<a href="javascript:void(0)" class="btn-action" onclick="orderStatusEdit(' . $data->id . ')" title="' . __('Change Status') . '" style="padding: 6px 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="fas fa-info-circle"></i></a>';

                    if (in_array($data->Order_Status, [ORDER_PENDING, ORDER_CANCELLED])) {
                        $btn = $btn . '<a href="' . route('admin.order_send_to_sms', encrypt($data->id)) . '" class="btn-action send-to-whatsapp" style="padding: 6px 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="fa-solid fa-sms"></i></a>';
                    }
                    if ($data->is_paid == 0 && strtoupper($data->Payment_Method) != 'COD') {
                        $btn = $btn . '<a href="' . route('admin.order_delete', encrypt($data->id)) . '" class="btn-action delete" style="padding: 6px 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><i class="fas fa-trash-alt"></i></a>';
                    }
                    $btn = $btn . '</div>';
                    return $btn;
                })
                ->addColumn('User', function ($data) {
                    return $data->user != null ? $data->user->name : __('Guest User');
                })
                ->addColumn('admin', function ($data) {
                    return $data->admin != null ? $data->admin->name : '-';
                })
                ->addColumn('phone_number', function ($data) {
                    if (is_null($data->billing_address)) {
                        return 'N/A';
                    }
                    $serialized_billing = $data->billing_address;

                    if (isset($serialized_billing['phone_number'])) {
                        return $serialized_billing['phone_number'];
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn("State", function ($data) {
                    if (is_null($data->billing)) {
                        return 'N/A';
                    }

                    return $data->billing->state_en;


                    // $order_state = State::where("id", $data->billing->State)->first();
                    // return $order_state['name_en'];
                })
                ->addColumn("City", function ($data) {
                    if (is_null($data->billing_address)) {
                        return 'N/A';
                    }


                    $serialized_billing = $data->billing_address;
                    if (isset($serialized_billing['city_ar'])) {
                        return $serialized_billing['city_ar'];
                    } else {
                        return 'N/A';
                    }
                    // $order_city = City::where('id', $data->billing->City)->first();
                    // return $order_city['name_en'];
                })
                ->addColumn('Subtotal', function ($data) {
                    return $data->Sub_Total . ' OMR';
                })
                ->addColumn('DeliveryCharge', function ($data) {
                    return $data->Delivery_Charge . ' OMR';
                })
                ->addColumn('GrandTotal', function ($data) {
                    return $data->Sub_Total - $data->Coupon_Amount + $data->Delivery_Charge . ' OMR';
                })
                ->addColumn("order_date", function ($data) {
                    return date('d-m-Y', strtotime($data->created_at));
                })
                ->addColumn('order_time', function ($data) {
                    return $data->created_at->timezone('Asia/Muscat')->format('h:i A');
                    // return date('h:i A', strtotime($data->created_at));
                })
                ->addColumn('Payment_Method', function ($data) {
                    $payment_method = strtolower($data->Payment_Method);
                    
                    // If Thawani and not paid yet, show "لم يتم الدفع" as requested by client
                    if (strtoupper($payment_method) === 'THAWANI' && ($data->is_paid == 0 || $data->Payment_Status === 'Unpaid')) {
                        return '<span style="color:red; font-weight: bold;">' . __('لم يتم الدفع') . '</span>';
                    }

                    if ($payment_method === 'cod') {
                        return '<span style="color:green; font-weight: bold;">' . strtoupper($payment_method) . '</span>';
                    } else {
                        return '<span style="color:blue; font-weight: bold;">' . ucfirst($payment_method) . '</span>';
                    }
                })
                ->addColumn('types', function ($data) {
                    $types = [];
                    foreach ($data->order_details as $key => $or) {
                        $product = $or->product;
                        if (is_null($product)) {
                            continue;
                        }
                        if ($product->type == PRODUCT_PHYSICAL) {
                            $types[] = 'Physical';
                        } elseif ($product->type == PRODUCT_DIGITAL) {
                            $types[] = 'Digital';
                        }
                    }

                    // Remove duplicates and return a comma-separated string or N/A
                    $types = array_unique($types);
                    return empty($types) ? 'N/A' : implode(',', $types);
                })
                ->addColumn('Coupon', function ($data) {
                    return is_null($data->Coupon_Id) ? 'N/A' : $data->coupon->CouponCode;
                })
                // ->addColumn('digital_goods', function ($data) {
                //     if (validDigitalSend($data->id)) {
                //         return '<a href="' . route('admin.digital_product_send', encrypt($data->id)) . '" class="btn btn-outline-primary small rounded" title="' . __('Send') . '">' . __('Send') . '</a>';
                //     } else {
                //         return 'N/A';
                //     }
                // })
                ->addColumn('order_source', function ($data) {
                    $source = $data->order_source ?? 'web';
                    if ($source == 'web') return __('Web');
                    if ($source == 'whatsapp') return __('WhatsApp');
                    if ($source == 'app') return __('App');
                    if ($source == 'admin') return __('Admin');
                    return __($source);
                })
                ->addColumn('collection_method', function ($data) {
                    $method = $data->collection_method ?? 'delivery';
                    if ($method == 'store_pickup') return __('Warehouse Pickup');
                    return __('Delivery');
                })
                ->addColumn('Status', function ($data) {
                    $html = '';
                    if ($data->Order_Status == ORDER_PENDING) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-clock mr-1"></i>' . __('Pending') . '</span>';
                    } elseif ($data->Order_Status == ORDER_PROCESSING) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-spinner mr-1"></i>' . __('Processing') . '</span>';
                    } elseif ($data->Order_Status == ORDER_SHIPPED) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-shipping-fast mr-1"></i>' . __('Shipped') . '</span>';
                    } elseif ($data->Order_Status == ORDER_DELIVERED) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-check-circle mr-1"></i>' . __('Delivered') . '</span>';
                    } elseif ($data->Order_Status == ORDER_CANCELLED) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-times-circle mr-1"></i>' . __('Cancel Order') . '</span>';
                    } elseif ($data->Order_Status == ORDER_RETURN) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-undo mr-1"></i>' . __('Cancel Order') . '</span>';
                    } elseif ($data->Order_Status == ORDER_NOT_PAYMENT_YET) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-money-bill-wave mr-1"></i>' . __('Not Payment Yet') . '</span>';
                    } elseif ($data->Order_Status == ORDER_DELIVERED_FAILED) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-exclamation-triangle mr-1"></i>' . __('Delivery Failed') . '</span>';
                    }
                    return $html;
                })
                ->rawColumns(['action', 'Status', 'types', 'Payment_Method'])
                ->make(true);
        }
        $data['title'] = __('Order List');
        $data['status_prefix'] = $status;
        return view('admin.pages.orders.list', $data);
    }

    public function order_details(Request $request)
    {
        $order = Order::query()
            ->with('order_details', 'user', 'coupon', 'order_details.product', 'billing', 'shipping')
            ->find($request->id);

        $order['billing_address'] = $order->billing_address;

        return view('admin.pages.orders.details', compact('order'));
    }

    public function order_print(Request $request)
    {
        // Printing should always be in English as requested
        app()->setLocale('en');

        $order = Order::query()
            ->with('order_details', 'user', 'coupon', 'order_details.product', 'billing', 'shipping')
            ->find($request->id);

        $order['billing_address'] = $order->billing_address;

        return view('admin.pages.orders.invoice', compact('order'));
    }

    public function orderStatusEdit(Request $request)
    {
        $order = Order::query()
            ->find($request->id);
        return view('admin.pages.orders.status', compact('order'));
    }


    public function orderStatusChange(Request $request, $id)
    {
        $id = decrypt($id);
        if (is_null($request->Order_Status)) {
            return redirect()->back()->with('error', __('Status is required!'));
        }
        $order = Order::whereId($id)->with('user')->first();
        if (!empty($order)) {
            // Prevent cancelling paid or COD orders
            if ($request->Order_Status == ORDER_CANCELLED && ($order->is_paid == 1 || $order->Payment_Method == COD)) {
                return redirect()->back()->with('error', __('Paid or COD orders cannot be cancelled!'));
            }

            // Delete unpaid Thawani orders completely upon cancellation
            if ($request->Order_Status == ORDER_CANCELLED && strtolower($order->Payment_Method) === 'thawani' && $order->is_paid == 0) {
                \App\Models\Admin\OrderDetails::where('Order_Id', $order->id)->delete();
                $order->delete();
                Log::info('Admin deleted unpaid Thawani order via cancellation', ['order_id' => $order->id]);
                return redirect()->back()->with('success', __('Order deleted successfully because it was an unpaid Thawani order!'));
            }

            // Check if status is changed to DELIVERED and it is a COD order
            if ($request->Order_Status == ORDER_DELIVERED && $order->Payment_Method == COD) {
                $order->is_paid = 1;
                $order->Payment_Status = PAYMENT_SUCCESS;
                
                // Sync to SmartLife ERP as PAID
                if (config('smartlife.sync_enabled')) {
                    try {
                        $smartLifeService = new \App\Services\SmartLifeErpService();
                        // This will trigger addPayment if invoice_id exists and status is paid
                        $smartLifeService->submitOrder($order);
                        Log::info('SmartLife Sync: COD order marked as Paid and synced on Delivery', ['order' => $order->Order_Number]);
                    } catch (\Exception $e) {
                        Log::error('SmartLife Sync failed during Admin COD Delivery update', ['error' => $e->getMessage()]);
                    }
                }
            }

            // Cancellation sync with SmartLife removed as per user request to keep records permanent
            /*
            if ($request->Order_Status == ORDER_CANCELLED && config('smartlife.sync_enabled') && $order->smartlife_invoice_id) {
                try {
                    $smartLifeService = new \App\Services\SmartLifeErpService();
                    $cancelled = $smartLifeService->cancelSaleViaReturn($order->smartlife_invoice_id);
                    if ($cancelled) {
                        Log::info('SmartLife Sync: Order cancelled via Return API', ['order' => $order->Order_Number, 'invoice_id' => $order->smartlife_invoice_id]);
                    }
                } catch (\Exception $e) {
                    Log::error('SmartLife Sync Cancellation Error: ' . $e->getMessage());
                }
            }
            */

            $update = $order->update([
                'Order_Status' => $request->Order_Status,
            ]);

            if (!empty($update)) {
                $url = "https://hispeed.om";
                if ($request->Order_Status == ORDER_DELIVERED)
                    $url = route('user.profile.track.my.order', ['id' => encrypt($order->id)]);

                $phoneNumber = $order->user->Number ?? null;
                if (empty($phoneNumber)) {
                    $billingAddress = $order->billing_address;
                    $phoneNumber = $billingAddress['phone_number'] ?? '';
                }
                
                $status_data = $order->getStatusLang()[$request->Order_Status] ?? null;
                $status_ar = $status_data['status_ar'] ?? 'N/A';

                // Updated WhatsApp API: hispeed.om and numeric order_id (as per curl provided)
                $response = Http::asForm()->post('https://whatsapi.hispeed.om/api/v1/whatsapp/change_status', [
                    'phone_number' => $phoneNumber,
                    'name' => $order->user->name ?? $billingAddress['name'] ?? '',
                    'order_id' => $order->id,
                    'status' => $status_ar,
                ]);

                if ($response->failed()) {
                    \Illuminate\Support\Facades\Log::error('WhatsApp Status Change API Error: ' . $response->body());
                }

                $this->statusChangeEmail($order, $request->Order_Status);
                return redirect()->back()->with('success', __('Status successfully changed!'));
            }
            return redirect()->back()->with('error', __('Something went wrong!'));
        }
        return redirect()->back()->with('error', __('Order not found!'));
    }


    public function bulkStatusUpdate(Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        $newStatus = $request->input('bulk_status');

        if (empty($orderIds) || empty($newStatus)) {
            return redirect()->back()->with('error', __('Please select orders and a status.'));
        }

        $orders = Order::whereIn('id', $orderIds)->get();
        foreach ($orders as $order) {
            // Update individual order status
            $order->update(['Order_Status' => $newStatus]);
            
            /*
            if ($newStatus == ORDER_CANCELLED && config('smartlife.sync_enabled') && $order->smartlife_invoice_id) {
                try {
                    $smartLifeService = new \App\Services\SmartLifeErpService();
                    $smartLifeService->cancelSaleViaReturn($order->smartlife_invoice_id);
                } catch (\Exception $e) {
                    Log::error('SmartLife Bulk Cancellation Error: ' . $e->getMessage());
                }
            }
            */
        }

        return redirect()->back()->with('success', __('Orders status updated successfully.'));

        return redirect()->back()->with('error', __('Failed to update orders status.'));
    }

    public function statusChangeEmail($order, $order_status)
    {
        $ship = $order->shipping_address;
        $data['userName'] = $ship['name'] ?? null;
        $data['userEmail'] = $ship['email'] ?? null;
        $data['order'] = $order;
        $data['companyName'] = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
        $data['subject'] = __('Shipment Process');
        $data['data'] = $order_status;
        $data['template'] = 'email.order-status-change';
        dispatch(new OrderConfirmMail($data))->onQueue('email-send');
    }

    public function orderDelete($id)
    {
        $id = decrypt($id);
        $order = Order::find($id);
        if (!$order) {
            return redirect()->back()->with('error', __('Order not found!'));
        }

        // Prevent deletion of Paid or COD orders
        if ($order->is_paid == 1 || strtoupper($order->Payment_Method) == 'COD') {
            return redirect()->back()->with('error', __('Paid or COD orders cannot be deleted!'));
        }

        $delete = $order->delete();
        if (!empty($delete)) {
            OrderDetails::where('Order_Id', $id)->delete();
            return redirect()->back()->with('success', __('Successfully Deleted!'));
        }
        return redirect()->back()->with('error', __('Something went wrong!'));
    }

    public function orderSendToSms($id)
    {
        $id = decrypt($id);
        $order = Order::whereId($id)->with('order_details.product')->first();

        try {
            $serialized_billing = $order->billing_address;
            
            $phoneNumber = null;
            if (isset($serialized_billing['phone_number'])) {
                $phoneNumber = $serialized_billing['phone_number'];
            } elseif ($order->user) {
                $phoneNumber = $order->user->Number;
            }

            if ($phoneNumber) {
                $smsService = new \App\Http\Services\MuscatAppsOtpService();
                $message = "عزيزي العميل، تم استلام طلبك بنجاح. رقم الطلب: {$order->Order_Number}. شكراً لتسوقك من خيرات الأنعام.";
                $success = $smsService->sendSms($phoneNumber, $message);
                
                if ($success) {
                    return redirect()->back()->with('success', __('Successfully Sent SMS!'));
                } else {
                    return redirect()->back()->with('error', __('Failed to send SMS.'));
                }
            } else {
                return redirect()->back()->with('error', __('No phone number found for this order.'));
            }
        } catch (\Exception $e) {
            \Log::error('Admin Order Send SMS Exception: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Something went wrong!'));
        }
    }


    //orderSendToWhatsapp

    public function transactionsList(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::where('Payment_Method', '!=', COD);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_email', function ($data) {
                    $email = $data->billing_address;
                    return $email['email'];
                })
                ->addColumn('GrandTotal', function ($data) {
                    return $data->Grand_Total . ' OMR';
                })
                ->addColumn('status', function ($data) {
                    $html = '';
                    if ($data->Order_Status == ORDER_PENDING) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-clock mr-1"></i>' . __('Pending') . '</span>';
                    } elseif ($data->Order_Status == ORDER_PROCESSING) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-spinner mr-1"></i>' . __('Processing') . '</span>';
                    } elseif ($data->Order_Status == ORDER_SHIPPED) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-shipping-fast mr-1"></i>' . __('Shipped') . '</span>';
                    } elseif ($data->Order_Status == ORDER_DELIVERED) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-check-circle mr-1"></i>' . __('Delivered') . '</span>';
                    } elseif ($data->Order_Status == ORDER_CANCELLED) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-times-circle mr-1"></i>' . __('Canceled') . '</span>';
                    } elseif ($data->Order_Status == ORDER_RETURN) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-undo mr-1"></i>' . __('Returned') . '</span>';
                    } elseif ($data->Order_Status == ORDER_NOT_PAYMENT_YET) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-money-bill-wave mr-1"></i>' . __('Not Payment Yet') . '</span>';
                    } elseif ($data->Order_Status == ORDER_DELIVERED_FAILED) {
                        $html = '<span class="badge badge-pill" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-exclamation-triangle mr-1"></i>' . __('Delivery Failed') . '</span>';
                    }
                    return $html;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        $data['title'] = __('All Transactions');
        return view('admin.pages.transactions.list', $data);
    }

    public function digitalProductSend($id)
    {
        $id = decrypt($id);
        $order = Order::whereId($id)->with('order_details', 'order_details.product')->first();
        if (!is_null($order)) {
            $data['title'] = __('Digital Product Send');
            $data['order'] = $order;
            return view('admin.pages.orders.digital-send', $data);
        }
        return redirect()->back()->with('error', __('No order found'));
    }

    public function digitalProductMail(Request $request)
    {
        $data['userName'] = 'John Doe';
        $data['userEmail'] = $request->mail_address;
        $data['data'] = $request->link;
        $data['subject'] = __('Digital Product Send');
        $data['template'] = 'email.digital-product-send';
        dispatch(new OrderConfirmMail($data))->onQueue('email-send');
        return redirect()->back()->with('success', __('Mail successfully send!'));
    }
}
