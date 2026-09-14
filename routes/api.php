<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\{
    ProductController,
    CategoryController,
    Auth\AuthController,
    CountryController,
    StateController,
    CityController,
    CouponController,
    TaxController,
    DeliveryController,
    CheckoutController,
    OrderController,
    UserController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('otp-signin', [AuthController::class, 'otpSignInPost']);
Route::post('otp-verify', [AuthController::class, 'otpVerifyPost']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products-with-discount', [ProductController::class, 'productsWithDiscount']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/subcategories/{category_id}', [CategoryController::class, 'getSubCategories']);
Route::get('/users', [UserController::class, 'index']);

Route::apiResource('countries', CountryController::class);
Route::apiResource('countries.states', StateController::class)->shallow();
Route::apiResource('countries.states.cities', CityController::class)->shallow();

//Route::post('checkout', [ThawaniPayController::class, 'checkout'])->name('checkout');
//Route::get('success', [ThawaniPayController::class, 'success'])->name('success');
//Route::get('fail', [ThawaniPayController::class, 'fail'])->name('fail');

Route::post('/calculate-tax', [TaxController::class, 'calculate']);
Route::post('/calculate-delivery-charge', [DeliveryController::class, 'calculateDeliveryCharge']);
Route::get('success', [CheckoutController::class, 'success'])->name('api.thawani.success');
Route::get('fail', [CheckoutController::class, 'fail'])->name('api.thawani.fail');
Route::get('/order-print/{id}', [OrderController::class, 'order_print'])->name('order.print');

// OMPAY Webhook
Route::post('/ompay/webhook', [\App\Http\Controllers\Api\OmpayWebhookController::class, 'handleWebhook'])->name('api.ompay.webhook');

Route::group(['middleware' => ['auth:sanctum', 'setLanguage']], function () {
    Route::post('/coupon-apply', [CouponController::class, 'couponApply']);
    Route::post('/checkout', [CheckoutController::class, 'checkoutOrder']);

});


// Delivery App Routes
Route::group(['prefix' => 'delivery'], function () {
    Route::post('auth/login', [\App\Http\Controllers\Api\DeliveryAuthController::class, 'login']);

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('auth/user', [\App\Http\Controllers\Api\DeliveryAuthController::class, 'user']);
        Route::post('auth/logout', [\App\Http\Controllers\Api\DeliveryAuthController::class, 'logout']);

        Route::get('orders', [\App\Http\Controllers\Api\DeliveryOrderController::class, 'index']);
        Route::get('my-orders', [\App\Http\Controllers\Api\DeliveryOrderController::class, 'myOrders']);
        Route::post('pick-order', [\App\Http\Controllers\Api\DeliveryOrderController::class, 'pickOrder']);
        Route::post('update-status', [\App\Http\Controllers\Api\DeliveryOrderController::class, 'updateStatus']);
        Route::get('history', [\App\Http\Controllers\Api\DeliveryOrderController::class, 'history']);
    });
});

// Printer App Routes
Route::group(['prefix' => 'printer'], function () {
    Route::post('login', [\App\Http\Controllers\Api\PrinterOrderController::class, 'login']);
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('orders', [\App\Http\Controllers\Api\PrinterOrderController::class, 'index']);
        Route::get('orders/{id}/print', [\App\Http\Controllers\Api\PrinterOrderController::class, 'print'])->name('printer.order.print');
        Route::post('update-printed-status', [\App\Http\Controllers\Api\PrinterOrderController::class, 'updatePrintedStatus']);
    });
});

// WhatsApp Store Routes
Route::group(['prefix' => 'whatsapp'], function () {
    Route::post('register', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'register']);
    Route::post('login', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'login']);
    Route::get('categories', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'getCategories']);
    Route::get('products', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'getProducts']);
    Route::get('catalog', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'getCatalog']);
    Route::get('products/{id}', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'getProductDetail']);
    Route::get('shipping-locations', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'getShippingLocations']);
    
    // Public PDF link (secure via ID but accessible for bot)
    Route::get('order-invoice/{id}/invoice.pdf', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'getOrderInvoicePdf'])->name('api.whatsapp.invoice_pdf');
    
    // Webhook for Bot to send user action (Convert to COD, Cancel)
    Route::post('order-action', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'handleOrderAction']);


    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('checkout', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'checkout']);
        Route::get('last-order', [\App\Http\Controllers\Api\Whatsapp\WhatsappStoreController::class, 'getLastOrder']);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
