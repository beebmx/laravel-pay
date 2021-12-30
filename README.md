# Laravel Pay

[![Build Status](https://travis-ci.org/beebmx/laravel-pay.svg?branch=master)](https://travis-ci.org/beebmx/laravel-pay)
[![Latest Stable Version](https://poser.pugx.org/beebmx/laravel-pay/v)](//packagist.org/packages/beebmx/laravel-pay)
[![License](https://poser.pugx.org/beebmx/laravel-pay/license)](//packagist.org/packages/beebmx/laravel-pay)

---
Laravel Pay

1. 
---

Laravel Pay is inspired by the official `Laravel Cashier`.
The approach is slightly different to `Cashier` and the main behavior is the payment transactions. 

## Installation

You can install the package via composer:

```bash
composer require beebmx/laravel-pay
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Beebmx\LaravelPay\PayServiceProvider" --tag="pay-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Beebmx\LaravelPay\PayServiceProvider" --tag="pay-config"
```

## Configuration

Before use `Laravel Pay` you need to add the `Payable` trait to your user model, the default location of this is in `App\Models\Users`:

```php
use Beebmx\LaravelPay\Payable;

class User extends Authenticatable {

    use Payable;
}
```

### Custom models

You can use your own models to fit your needs, just set in the `App\Providers\AppServiceProvider` and inform Laravel Pay in the `boot` method:

```php
use App\Models\Address;
use App\Models\Pay\User;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Beebmx\LaravelPay\Pay;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Pay::useCustomerModel(User::class);
    Pay::useAddressModel(Address::class);
    Pay::useTransactionModel(Transaction::class);
    Pay::useTransactionItemModel(TransactionItem::class);
}
```

Just remember, if you change this for your own models, make sure to extend these models with the `Laravel Pay`:

```php
use Beebmx\LaravelPay\Address as PayAddress;

class Address extends PayAddress
{
    //
}
```

## Drivers

### Sandbox

This works as a playground, with full capabilities as a testing or production driver.

### Stripe

To work with the `stripe` driver you need to set as default driver and configurate your `testing` or `production` keys:

```php
<?php

return [
    'default' => 'stripe',
    
    'drivers' => [
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
    ]
];
```

### Conekta

To work with the `conekta` driver you need to set as default driver and configurate your `testing` or `production` keys:

```php
<?php

return [
    'default' => 'conekta',
    
    'drivers' => [
        'conekta' => [
            'driver' => Beebmx\LaravelPay\Drivers\ConektaDriver::class,
            'secret' => env('CONEKTA_SECRET_KEY', null),
            'public' => env('CONEKTA_PUBLIC_KEY', null),
            'webhooks' => [
                'handler' => Beebmx\LaravelPay\Http\Webhooks\ConektaHandler::class,
            ],
        ],
    ]
];
```

## Customer

### Create customer

A `user` can be a customer (this is not a requirement) and will create a customer in the correspond driver.
To create a customer just use the method `createCustomerWithDriver`:

```php
$customer = $user->createCustomerWithDriver();
```

> Creating a user can be useful for recurring payments or to avoid asking for payment methods all the time.

## Payment Methods

To add a payment method, first you need to have a customer on the driver platform. After you have your customer just add the right `token` or `payment_method`:

````php
$paymentMethod = $user->addPaymentMethod('pm_card_visa');
````

## Shipping

If your payment have a shipping, just add the proper address before perform the payment:

```php
$address = $user->addresses->first();
$user->shipping($address);
```

> Remember that this package have an `address` model build in out of the box.

## Payments

To perform a payment is as easy as:

```php
$user->charge([
    'name' => 'Product',
    'price' => 500,
]);
```

If you have more than one product or service:

```php
$user->charge([[
    'name' => 'Product 01',
    'price' => 100
], [
    'name' => 'Product 02',
    'price' => 200
]]);
```

> It's important that the user has at least one `payment method`.

If you have a shipping address:

```php
$address = $user->addresses->first();

$user
    ->shipping($address)
    ->charge([
        'name' => 'Testing product',
        'price' => 500,
    ]);
```

If you don't need to create a `payment method` you can create a `charge` with a one time payment `token`:

```php
$user
    ->token('tok_visa4242')
    ->charge([
        'name' => 'Testing product',
        'price' => 500,
    ]);
```

> If you are using the `token` option, the `createCustomerWithDriver` is optional and not required.

If you need the payment `transaction`, you can retrive it with the `getTransaction()` method:

```php
$payment = $user->charge([
    'name' => 'Product',
    'price' => 500,
]);

$payment->getTransaction()
```

## Webhooks

By default `Laravel Pay` provides a handy management of webhooks.

First in your `App\Http\Middleware\VerifyCsrfToken` file, you need to allow the path of the WebhookController:

```php
protected $except = [
    'pay/webhooks',
];
```

> The default path is `pay/webhooks` but you can change it in the config file, updating the `pay.path` and `pay.webhooks`.

You can create manually your endpoint in your driver provider site or with the command:

```ssh
php artisan pay:webhook
```

> The driver `sandbox` doesn't allow the creation of webhooks.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Fernando Gutierrez](https://github.com/beebmx)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.