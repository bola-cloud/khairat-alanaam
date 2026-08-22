<?php

use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\CityController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\AuthController;
use App\Http\Controllers\Frontend\AboutUsController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\BlogCommentController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BuyNowController;
use App\Http\Controllers\Frontend\CouponController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Frontend\SubscribeController;
use App\Http\Controllers\Frontend\CompareListController;
use App\Http\Controllers\Frontend\UserProfileController;
use App\Http\Controllers\Frontend\ServiceCustomerController;
use App\Http\Controllers\Frontend\SubscribeSessionController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Http\Controllers\Frontend\NewDesignController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Models\Admin\Category;
use App\Models\Admin\Order;
use App\Models\Admin\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use League\Csv\Reader;


//Route::redirect('/', '');

Route::post('currency-price', [CartController::class, 'currencyPrice'])->name('currency_price');
Route::get('currency-symbol', [CartController::class, 'currencySymbol'])->name('currency_symbol');
Route::group(['middleware' => ['is_user']], function () {
    Route::get('/olddesign', [HomeController::class, 'index'])->name('front.olddesign');
    Route::get('/', [NewDesignController::class, 'index'])->name('front');
    Route::get('/store', [NewDesignController::class, 'store'])->name('front.store');
    Route::get('/wholesale-orders', [NewDesignController::class, 'wholesale'])->name('wholesale.orders');
    Route::post('/wholesale-orders', [NewDesignController::class, 'storeWholesaleRequest'])->name('wholesale.orders.store');
    Route::get('/become-partner', [NewDesignController::class, 'become_partner'])->name('become.partner');
    Route::post('/become-partner', [NewDesignController::class, 'storePartnerRequest'])->name('become.partner.store');
    Route::get('/social-responsibility', [NewDesignController::class, 'social_responsibility'])->name('social.responsibility');
    Route::get('/trial-boxes', [NewDesignController::class, 'trial_boxes'])->name('trial.boxes');
    Route::get('/subscriptions', [NewDesignController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/custom-box', [NewDesignController::class, 'custom_box'])->name('custom.box');
    Route::get('/coffee-crops', [NewDesignController::class, 'coffee_crops'])->name('coffee.crops');
    Route::get('/technical-tools', [NewDesignController::class, 'technical_tools'])->name('technical.tools');
    Route::get('/experts', [NewDesignController::class, 'experts'])->name('experts');
    Route::post('/experts', [NewDesignController::class, 'storeExpertRequest'])->name('experts.store');
    Route::get('/monthly-offers', [NewDesignController::class, 'monthly_offers'])->name('monthly.offers');
    Route::get('/gift-cards', [NewDesignController::class, 'gift_cards'])->name('gift.cards');
    Route::post('/gift-card/purchase', [NewDesignController::class, 'purchaseGiftCard'])->name('gift_card.purchase');
    Route::get('/gift-card/success', [NewDesignController::class, 'giftCardSuccess'])->name('gift_card.success');
    Route::get('/gift-card/cancel', [NewDesignController::class, 'giftCardCancel'])->name('gift_card.cancel');
    Route::get('/contact-us', [NewDesignController::class, 'contact_us'])->name('contact.us');
    Route::post('/contact-us', [NewDesignController::class, 'contact_us_store'])->name('contact.us.store');
    Route::get('/login', [NewDesignController::class, 'login'])->name('login');
    Route::get('/register', [NewDesignController::class, 'register'])->name('user.sign.up');
    Route::get('/product/{slug}', [NewDesignController::class, 'product_details'])->name('front.product_details');
    Route::get('/cart', [NewDesignController::class, 'cart'])->name('front.cart');
    Route::get('/theme-set/{theme}', [HomeController::class, 'theme_set']);
    Route::get('locale/{lang}', [HomeController::class, 'localeSwitch'])->name('locale.switch');
    Route::get('currency/{amount}', [HomeController::class, 'currencySwitch'])->name('currency.switch');
    Route::post('subscribe', [SubscribeController::class, 'subscribe'])->name('subscribe');

    //session value store get and delete
    Route::get('do_not_subscribe', [SubscribeSessionController::class, 'doNotSubscribe'])->name('do.not.subscribe');
    Route::get('get_session', [SubscribeSessionController::class, 'doNotSubscribeGet']);
    Route::get('remove_session', [SubscribeSessionController::class, 'doNotSubscribeRemove']);

    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', [BlogController::class, 'index'])->name('blog');
        Route::get('/blog-details/{id}', [BlogController::class, 'blogDetails'])->name('blog.details');
        Route::post('/blog-comment', [BlogCommentController::class, 'blogComment'])->name('user.blog.comment')->middleware(['isDemo']);
    });

    Route::get('/payments/approval', [CheckoutController::class, 'approval'])->name('approval');
    Route::get('/payments/cancelled', [CheckoutController::class, 'cancelled'])->name('cancelled');
    Route::get('/stripe-collapse', [PaymentController::class, 'stripeCollapse'])->name('stripe_collapse');

    Route::get('about-us', [AboutUsController::class, 'aboutUS'])->name('about.us');

    Route::group(['prefix' => 'user/'], function () {
        //User Sign-in and Sign-up
        // Route::get('sign-in', [AuthController::class, 'userSignIn'])->name('login'); // Replaced with NewDesignController
        Route::post('sign-in', [AuthController::class, 'userSignInPost'])->name('user.sign.in.post');
        Route::post('otp', [AuthController::class, 'otpSignInPost'])->name("user.sign.otp");
        Route::get('otp-verify', [AuthController::class, 'otpVerify'])->name("user.otp.verify.get");
        Route::post('otp-verify', [AuthController::class, 'otpVerifyPost'])->name("user.otp.verify");
        Route::get("complete-registration", [AuthController::class, 'completeRegistration'])->name("user.complete.registration");
        Route::post('login-modal', [AuthController::class, 'loginModal'])->name('user.sign.modal');
        // Route::get('sign-up', [AuthController::class, 'userSignUp'])->name('user.sign.up'); // Replaced with NewDesignController
        Route::post('sign-up', [AuthController::class, 'userSignUpPost'])->name('user.sign.up.post');
        Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('user.redirect_google');
        Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('user.handle_google_callback');
        Route::get('auth/facebook', [AuthController::class, 'redirectToFacebook'])->name('user.redirect_facebook');
        Route::get('auth/facebook/callback', [AuthController::class, 'handleFacebookCallback'])->name('user.handle_facebook_callback');
        Route::get('logout', [AuthController::class, 'userLogout'])->name('user.logout');
        
        // Email verification
        Route::get('verify-whatsapp', [AuthController::class, 'showVerifyEmail'])->name('user.verify.email');
        Route::post('verify-whatsapp', [AuthController::class, 'verifyEmailPost'])->name('user.verify.email.post');
        Route::get('resend-otp', [AuthController::class, 'resendOtp'])->name('user.resend.otp');

        Route::post('change-password', [AuthController::class, 'userChangePassword'])->name('user.profile.change.password')->middleware(['isDemo']);
        //forget password
        Route::get('forget-password', [AuthController::class, 'userForgetPasswordGet'])->name('forget.password.get');
        Route::post('forget-password', [AuthController::class, 'userForgetPasswordPost'])->name('forget.password.post')->middleware(['isDemo']);
        Route::get('forget-password/otp', [AuthController::class, 'userForgetPasswordOtp'])->name('forget.password.otp');
        Route::post('forget-password/otp', [AuthController::class, 'userForgetPasswordOtpVerify'])->name('forget.password.otp.post');
        Route::get('forget-password/reset', [AuthController::class, 'userShowResetPasswordForm'])->name('reset.password.get');
        Route::post('forget-password/reset', [AuthController::class, 'submitResetPasswordForm'])->name('reset.password.post')->middleware(['isDemo']);
        Route::get('forget-password/success', [AuthController::class, 'userResetPasswordSuccess'])->name('reset.password.success');

        Route::get('contact-us', [NewDesignController::class, 'contactUs'])->name('contact.us');
        Route::post('contact-us', [NewDesignController::class, 'contactUsSend'])->name('contact.us.send');
        Route::get('about-us', [NewDesignController::class, 'aboutUs'])->name('about.us');

        Route::group(['middleware' => 'auth'], function () {
            // V2 Profile Routes
            Route::get('profile', [NewDesignController::class, 'profile'])->name('user.profile');
            Route::get('profile/addresses', [NewDesignController::class, 'profileAddresses'])->name('user.profile.addresses');
            Route::get('profile/orders', [NewDesignController::class, 'profileOrders'])->name('user.profile.orders');
            Route::get('profile/favorites', [NewDesignController::class, 'profileFavorites'])->name('user.profile.favorites');
            Route::get('profile/orders/{id}', [NewDesignController::class, 'trackOrder'])->name('user.profile.order.track');

            Route::post('profile-update', [\App\Http\Controllers\Frontend\ProfileSettingsController::class, 'update'])->name('user.profile.update')->middleware(['isDemo']);
            Route::post('orders/{orderNumber}/reorder', [\App\Http\Controllers\Frontend\ProfileOrdersController::class, 'reorder'])->name('user.profile.orders.reorder');
            Route::post('review-store', [\App\Http\Controllers\Frontend\ProfileReviewsController::class, 'store'])->name('user.profile.review_store')->middleware(['isDemo']);

            // User Addresses CRUD
            Route::post('addresses', [\App\Http\Controllers\Frontend\ProfileAddressController::class, 'store'])->name('user.profile.addresses.store');
            Route::put('addresses/{id}', [\App\Http\Controllers\Frontend\ProfileAddressController::class, 'update'])->name('user.profile.addresses.update');
            Route::delete('addresses/{id}', [\App\Http\Controllers\Frontend\ProfileAddressController::class, 'destroy'])->name('user.profile.addresses.destroy');

            // Subscription Payment
            Route::post('subscription/pay', [\App\Http\Controllers\Frontend\SubscriptionPaymentController::class, 'initiatePayment'])->name('user.subscription.pay');
            Route::get('subscription/callback', [\App\Http\Controllers\Frontend\SubscriptionPaymentController::class, 'paymentCallback'])->name('user.subscription.callback');

            // Order Invoice/Print
            Route::get('order-print/{id}', [\App\Http\Controllers\Frontend\OrderController::class, 'order_print'])->name('order.print');

            // wishlist
            Route::group(['prefix' => 'wishlist'], function () {
                Route::get('/', [WishlistController::class, 'Wishlist'])->name('wishlist');
                Route::get('delete', [WishlistController::class, 'delete'])->name('wishlist.delete');
            });

            // V2 Favorites
            Route::get('/my-favorites', [\App\Http\Controllers\Frontend\NewDesignController::class, 'favorites'])->name('front.v2.favorites');

            // Recipe Details
            Route::get('/recipe/{slug}', [\App\Http\Controllers\Frontend\NewDesignController::class, 'recipeDetails'])->name('front.v2.recipe.details');

            // comparelist
            Route::group(['prefix' => 'compare'], function () {
                Route::get('', [CompareListController::class, 'Comparelist'])->name('compare');
                Route::get('delete', [CompareListController::class, 'delete'])->name('compare.delete')->middleware(['isDemo']);
            });

            // user addresses (frontend)
            Route::group(['prefix' => 'addresses'], function () {
                Route::get('/', [\App\Http\Controllers\Frontend\AddressController::class, 'index'])->name('addresses.index');
                Route::post('/', [\App\Http\Controllers\Frontend\AddressController::class, 'store'])->name('addresses.store');
                Route::put('/{address}', [\App\Http\Controllers\Frontend\AddressController::class, 'update'])->name('addresses.update');
                Route::delete('/{address}', [\App\Http\Controllers\Frontend\AddressController::class, 'destroy'])->name('addresses.destroy');
                Route::post('/{address}/default', [\App\Http\Controllers\Frontend\AddressController::class, 'setDefault'])->name('addresses.setDefault');
            });
        });
        Route::get('compare/add', [CompareListController::class, 'add'])->name('compare.add')->middleware(['isDemo']);
        Route::get('wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add')->middleware(['isDemo']);
    });

    Route::group(['middleware' => 'auth'], function () {
        Route::post('profile/addresses', [\App\Http\Controllers\Frontend\ProfileAddressController::class, 'store'])->name('profile.addresses.store');
        Route::put('profile/addresses/{id}', [\App\Http\Controllers\Frontend\ProfileAddressController::class, 'update'])->name('profile.addresses.update');
        Route::delete('profile/addresses/{id}', [\App\Http\Controllers\Frontend\ProfileAddressController::class, 'destroy'])->name('profile.addresses.destroy');
        Route::post('profile/orders/{orderNumber}/reorder', [\App\Http\Controllers\Frontend\ProfileOrdersController::class, 'reorder'])->name('profile.orders.reorder');
    });

    Route::group(['prefix' => 'cart'], function () {
        Route::post('add', [CartController::class, 'addToCart'])->name('add.to.cart');
        Route::post('add-custom-box', [CartController::class, 'addCustomBoxToCart'])->name('add.custom.box');
        Route::get('/content', [CartController::class, 'cartContent'])->name('cart.content');
        Route::get('/delete', [CartController::class, 'cartDelete'])->name('cart.delete');
        Route::get('/decrease', [CartController::class, 'cartDecrease'])->name('cart.decrease');
        Route::get('/increase', [CartController::class, 'cartIncrease'])->name('cart.increase');
    });
    Route::post('buy-now', [BuyNowController::class, 'buyNow'])->name('buy.now');

    Route::group(['prefix' => 'product'], function () {
        Route::get('single/{slug}', [ProductController::class, 'singleProduct'])->name('single.product');
        // New-design product detail preview route (keeps old controller logic but returns new blade)
        Route::get('single-new/{slug}', [ProductController::class, 'singleProductNewDesign'])->name('single.product.new');
        Route::get('all', [ProductController::class, 'allProduct'])->name('all.product');
        Route::get('all/left-sidebar', [ProductController::class, 'productListLeftSidebar'])->name('product.list.left.sidebar');
        Route::get('shorting', [ProductController::class, 'productSorting'])->name('product.shorting');
        Route::get('filter', [ProductController::class, 'productFiltering'])->name('product.filtering');
        Route::get('left-shorting', [ProductController::class, 'productSortingLeftSide'])->name('product.shorting.left.side');
        Route::get('filter/left-side', [ProductController::class, 'productFilteringLeftSide'])->name('product.filtering.left.side');
        Route::get('category/{id?}', [ProductController::class, 'CategoryWiseProduct'])->name('category.product');
        Route::get('category/left/{id}', [ProductController::class, 'CategoryWiseProductLeft'])->name('category.product_left');
        Route::get('brand/{id}', [ProductController::class, 'BrandWiseProduct'])->name('brand.product');
        Route::get('brand/left/{id}', [ProductController::class, 'BrandWiseProductLeft'])->name('brand.product_left');
        // product reviews
        Route::post('{product}/review', [ProductController::class, 'storeReview'])->name('product.review.store');
    });

    Route::get('terms/conditions', [ServiceCustomerController::class, 'termsConditionsNewDesign'])->name('terms.conditions');
    // Route::get('privacy/policy', [ServiceCustomerController::class, 'privacyPolicy'])->name('privacy.policy');
    // New-design versions (dynamic content managed from admin customer services)
    Route::get('terms/conditions-new', [ServiceCustomerController::class, 'termsConditionsNewDesign'])->name('terms.conditions.new');
    Route::get('privacy/policy', [ServiceCustomerController::class, 'privacyPolicyNewDesign'])->name('privacy.policy');
    Route::get('privacy/policy-new', [ServiceCustomerController::class, 'privacyPolicyNewDesign'])->name('privacy.policy.new');
    Route::get('shipping/return', [ServiceCustomerController::class, 'shippingReturn'])->name('shipping.return');
    // New-design Shipping & Return page
    Route::get('shipping/return-new', [ServiceCustomerController::class, 'shippingReturnNewDesign'])->name('shipping.return.new');
    Route::get('faq', [ServiceCustomerController::class, 'Faq'])->name('faq');
    Route::get('refund/policy', [ServiceCustomerController::class, 'refundPolicy'])->name('refund.policy');

    Route::group(['prefix' => 'category'], function () {
        Route::get('search', [ProductController::class, 'CategorySearchProduct'])->name('category.search');
    });

    Route::group(['prefix' => 'checkout'], function () {
        Route::get('/{buyFor?}', [CheckoutController::class, 'checkoutPage'])->name('checkout');
        Route::post('order', [CheckoutController::class, 'checkoutOrder'])->name('checkout.order');
        Route::post('guest-order', [CheckoutController::class, 'guestCheckoutOrder'])->name('guest.checkout.order');
        Route::post('get-tax-amount', [CheckoutController::class, 'getTaxAmount'])->name('checkout.get_tax_amount');
        Route::get('thank-you', [CheckoutController::class, 'thankyouPage'])->name('checkout.thankyou_page');
    });
    Route::group(['prefix' => 'coupon'], function () {
        Route::post('apply', [CouponController::class, 'couponApply'])->name('apply.coupon');
    });

    Route::get('/page/{slug}', [PageController::class, 'singlePage'])->name('page.single');
    Route::get('/page/{slug}', [PageController::class, 'singlePage'])->name('page.single');
    Route::post('/order-track', [CheckoutController::class, 'orderTrack'])->name('checkout.order_track');

    // Subscription Payment Routes
    Route::post('/subscription/pay', [App\Http\Controllers\Frontend\SubscriptionPaymentController::class, 'initiatePayment'])->name('user.subscription.pay');
    Route::get('/subscription/callback', [App\Http\Controllers\Frontend\SubscriptionPaymentController::class, 'paymentCallback'])->name('user.subscription.callback');
});

Route::match(array('GET', 'POST'), '/payment-notify/{id}', [PaymentApiController::class, 'paymentNotifier'])->name('paymentNotify');
Route::match(array('GET', 'POST'), 'payment-cancel/{id}', [PaymentApiController::class, 'paymentCancel'])->name('paymentCancel');

// Payment gateway callback routes (user redirect after payment)
Route::get('/payment/callback/success', [\App\Http\Controllers\PaymentCallbackController::class, 'success'])->name('payment.callback.success');
Route::get('/payment/callback/cancel', [\App\Http\Controllers\PaymentCallbackController::class, 'cancel'])->name('payment.callback.cancel');

// Thawani payment webhook (server-to-server notification from Thawani gateway)
Route::post('/payment/webhook/thawani', [\App\Http\Controllers\ThawaniWebhookController::class, 'handle'])->name('thawani.webhook');

// SSLCOMMERZ Start
Route::post('/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);
Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);
//SSLCOMMERZ END


// Thawani pay
Route::get("/thawani-success", [CheckoutController::class, "paymentSuccess"])->name("thawani.success");
Route::get("/thawani-cancel", [CheckoutController::class, "paymentCancel"])->name("thawani.cancel");
// In web.php

Route::get("/get-cities-by-state/{state_id}", [CityController::class, "getCitiesByState"]);
Route::get("/get-city-charge/{city_id}", [CityController::class, "getCityCharge"]);
Route::get("/get-area-charge/{area_id}", [CityController::class, "getAreaCharge"]);
Route::get("/get-areas-by-city/{city_id}", [CityController::class, "getAreasByCity"]);

Route::get('/search/suggest', [ProductController::class, 'autoSuggest'])->name('search.suggest');


// Category listing (simple frontend page)
Route::get('/categories/{slug?}', [CategoryController::class, 'show'])->name('categories.show');

Route::get("find-test-order", function () {
    $products = Product::all();
    foreach ($products as $product) {
        $product->Discount_Price = $product->Price;
        $product->Discount = 0;
        $product->save();
    }
    return "oky";
});

Route::get('/debug-api-v4', function () {
    return "Route V4 is Working. Time: " . date('H:i:s');
});

// TEMP: Preview printable invoice in browser (remove after testing)
Route::get('/preview-invoice/{id?}', function ($id = null) {
    $order = $id
        ? Order::with(['order_details.product', 'user'])->findOrFail($id)
        : Order::with(['order_details.product', 'user'])->latest()->first();
    if (!$order) return 'No orders found';
    return view('orders.printableInvoice', compact('order'));
});



// Debug ERP Sync Route
Route::get('/debug-erp/{order_number}', function ($order_number) {
    if (!auth()->check() || !auth()->user()->is_admin) {
        // Simple auth check for safety, though it's debug
        // return "Admin login required";
    }

    $order = Order::where('Order_Number', $order_number)->with('order_details')->first();
    if (!$order) {
        return "Order number {$order_number} not found.";
    }

    $logs = [];
    $logs[] = "Found Order ID: {$order->id}, Number: {$order->Order_Number}, Status: {$order->Payment_Status}";

    try {
        $erp = new \App\Services\SmartLifeErpService();
        $logs[] = "Service instantiated.";

        if (!$erp->testConnection()) {
            return response()->json(['error' => 'Connection to ERP failed', 'logs' => $logs]);
        }
        $logs[] = "Connection successful.";

        $products = [];
        $logs[] = "Processing " . count($order->order_details) . " items...";

        foreach ($order->order_details as $detail) {
            $logItem = "Item: {$detail->Product_Name} (Qty: {$detail->Quantity}) - ";

            $smartLifeId = null;
            $barcode = null;

            // 1. Direct ID
            $product = \App\Models\Admin\Product::find($detail->Product_Id);
            if ($product) {
                $logItem .= "Local Product Found (ID: {$product->id}, Barcode: {$product->barcode}). ";
                if ($product->smartlife_id) {
                    $smartLifeId = $product->smartlife_id;
                    $logItem .= "Mapped via smartlife_id ($smartLifeId). ";
                } elseif ($product->barcode) {
                    $barcode = $product->barcode;
                    $shadow = \App\Models\SmartLifeProduct::where('barcode', $barcode)->first();
                    if ($shadow) {
                        $smartLifeId = $shadow->smartlife_id;
                        $logItem .= "Mapped via Barcode Shadow Table ($smartLifeId). ";
                    } else {
                        $logItem .= "Barcode OK but NOT in Shadow Table. ";
                    }
                } else {
                    $logItem .= "No Barcode/ID on Local Product. ";
                }
            } else {
                $logItem .= "Local Product Deleted/Missing. ";
            }

            // 2. Fallback Name
            if (!$smartLifeId) {
                $logItem .= "Trying Name Fallback... ";
                $smartLifeProduct = \App\Models\SmartLifeProduct::where('name', 'LIKE', '%' . $detail->Product_Name . '%')->first();
                if ($smartLifeProduct) {
                    $smartLifeId = $smartLifeProduct->smartlife_id;
                    $barcode = $smartLifeProduct->barcode ?? $barcode;
                    $logItem .= "FOUND via Name Match ($smartLifeId). ";
                } else {
                    $logItem .= "Name Match Failed. ";
                }
            }

            if ($smartLifeId) {
                $products[] = [
                    'id' => $smartLifeId,
                    'barcode' => $barcode ?? '0000',
                    'name' => $detail->Product_Name,
                    'price' => (float) $detail->Price,
                    'quantity' => (int) $detail->Quantity,
                ];
                $logs[] = $logItem . " -> ADDED TO PAYLOAD.";
            } else {
                $logs[] = $logItem . " -> SKIPPED (No valid mapping).";
            }
        }

        // Check Warehouses
        try {
            $token = $erp->getAccessToken();
            $apiUrl = config('smartlife.api_url');
            $warehouseRes = Http::withHeaders(['Authorization' => $token])->get("{$apiUrl}/warehouses");

            if ($warehouseRes->successful()) {
                $logs[] = "Warehouses Found: " . json_encode($warehouseRes->json()['data'] ?? 'No data key');
            } else {
                $logs[] = "Check Warehouses Failed: " . $warehouseRes->status() . " (Endpoint might be different)";
            }
        } catch (\Exception $e) {
            $logs[] = "Warehouse Check Exception: " . $e->getMessage();
        }

        if (request()->has('submit') && request()->get('submit') == 'true') {
            if (!empty($products)) {
                $logs[] = "Submitting to ERP...";

                $customerId = $order->user ? $order->user->smartlife_customer_id : 6;
                $saleDetails = [
                    'order_reference' => $order->Order_Number,
                    'notes' => 'Debug Sync ' . $order->Order_Number,
                    'status' => 'final',
                    'payment_status' => 'paid',
                ];

                // Add warehouse_id if passed in URL
                if (request()->has('warehouse_id')) {
                    $saleDetails['warehouse_id'] = request()->get('warehouse_id');
                }

                $result = $erp->addSale($products, $customerId, $saleDetails);
                $logs[] = "Submission Result: " . json_encode($result);
            } else {
                $logs[] = "No products to submit.";
            }
        } else {
            $logs[] = "Dry Run. Add ?submit=true to sync. Add &warehouse_id=X to specify warehouse.";
        }

        return response()->json(['success' => true, 'payload_preview' => $products, 'logs' => $logs]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});
