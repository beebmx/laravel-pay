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
            'secret' => env('STRIPE_SECRET_KEY'),
            'public' => env('STRIPE_PUBLIC_KEY'),
            'webhooks' => [
                'events' => [
                    'customer.subscription.created',
                    'customer.subscription.updated',
                    'customer.subscription.deleted',
                    'customer.updated',
                    'customer.deleted',
                    'invoice.payment_action_required',
                ]
            ],
        ],

        'conekta' => [
            'driver' => Beebmx\LaravelPay\Drivers\ConektaDriver::class,
            'secret' => env('CONEKTA_SECRET_KEY'),
            'public' => env('CONEKTA_PUBLIC_KEY'),
            'webhooks' => [

            ],
        ],
    ],

    'locale' => 'en',

    'path' => env('PAY_PATH', 'pay'),

    'webhooks' => env('PAY_WEBHOOKS_PATH', 'webhooks'),

    'oxxo' => [
        'days_to_expire' => env('CONEKTA_OXXO_EXPIRES_AT', 30),
    ],


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
