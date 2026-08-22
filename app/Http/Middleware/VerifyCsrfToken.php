<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/checkout/order','/checkout/guest-order', '/success','/cancel','/fail','/ipn',
        '/payment/webhook/thawani',
        'admin/general-settings/update-settings',
        'admin/general-settings/update-email',
        'admin/general-settings/update-social-login',
    ];
}
