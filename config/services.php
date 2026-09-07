<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'paypal' => [
        'base_uri' => env('PAYPAL_SANDBOX') == 1 ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com',
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'class' => App\Http\Services\PaypalService::class,
    ],

    'stripe' => [
        'base_uri' => env('STRIPE_BASE_URI'),
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'class' => App\Http\Services\StripeService::class,
    ],

    'razorpay' => [
        'base_uri' => env('RAZORPAY_BASE_URI'),
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'class' => App\Http\Services\StripeService::class,
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_CALLBACK_URL'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_CALLBACK_URL'),
    ],
    'thawani' => [
        'secret_key' => env('THAWANI_TEST_SECRET_KEY'),
        'public_key' => env('THAWANI_TEST_PUBLIC_KEY'),
        'checkout_url' => env('THAWANI_TEST_CHECKOUT_URL'),
        'pay_url' => env('THAWANI_TEST_PAY_URL'),
        'webhook_secret' => env('THAWANI_WEBHOOK_SECRET'),
    ],
    'onesignal' => [
        'app_id' => env('ONESIGNAL_APP_ID'),
        'rest_api_key' => env('ONESIGNAL_REST_API_KEY'),
        'android_channel_id' => env('ONESIGNAL_ANDROID_CHANNEL_ID'),
    ],
    'ompay' => [
        'api_key' => env('OMPAY_API_KEY'),
        'api_secret' => env('OMPAY_API_SECRET'),
        'base_url' => env('OMPAY_BASE_URL', 'https://api.sandbox.truepay.ompay.om'),
        'class' => App\Http\Services\OmpayService::class,
    ],

];
