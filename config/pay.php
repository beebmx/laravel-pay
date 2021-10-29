<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | Supported drivers: 'sandbox', 'stripe', 'conekta
    |
    */

    'default' => env('PAY_DRIVER', 'sandbox'),

    'drivers' => [
        'sandbox' => [
            'driver' => Beebmx\LaravelPay\Drivers\SandboxDriver::class,
            'webhooks' => null,
        ],

        'stripe' => [
            'driver' => Beebmx\LaravelPay\Drivers\StripeDriver::class,
            'webhooks' => null,
            'secret' => env('STRIPE_SECRET_KEY'),
            'public' => env('STRIPE_PUBLIC_KEY'),
        ],

        'conekta' => [
            'driver' => Beebmx\LaravelPay\Drivers\ConektaDriver::class,
            'webhooks' => null,
            'secret' => env('CONEKTA_SECRET_KEY'),
            'public' => env('CONEKTA_PUBLIC_KEY'),
            'days_to_expire' => env('CONEKTA_OXXO_EXPIRES_AT', 2),
        ],
    ],

    'locale' => 'en',


    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency to generate charges from your application.
    |
    */

    'currency' => env('PAY_CURRENCY', 'usd')

];
