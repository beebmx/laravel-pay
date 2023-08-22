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
            'secret' => env('STRIPE_SECRET_KEY', null),
            'public' => env('STRIPE_PUBLIC_KEY', null),
            'webhooks' => [
                'handler' => Beebmx\LaravelPay\Http\Webhooks\StripeHandler::class,
                'events' => [
                    'customer.deleted',
                    'customer.updated',
                    'invoice.payment_action_required',
                    'payment_intent.canceled',
                    'payment_intent.payment_failed',
                    'payment_intent.requires_action',
                    'payment_intent.succeeded',
                ],
            ],
        ],

        'conekta' => [
            'driver' => Beebmx\LaravelPay\Drivers\ConektaDriver::class,
            'secret' => env('CONEKTA_SECRET_KEY', null),
            'public' => env('CONEKTA_PUBLIC_KEY', null),
            'webhooks' => [
                'handler' => Beebmx\LaravelPay\Http\Webhooks\ConektaHandler::class,
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

    'currency' => env('PAY_CURRENCY', 'usd'),

];
