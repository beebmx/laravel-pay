# Laravel Pay

[![Build Status](https://travis-ci.org/beebmx/laravel-pay.svg?branch=master)](https://travis-ci.org/beebmx/laravel-pay)
[![Latest Stable Version](https://poser.pugx.org/beebmx/laravel-pay/v)](//packagist.org/packages/beebmx/laravel-pay)
[![License](https://poser.pugx.org/beebmx/laravel-pay/license)](//packagist.org/packages/beebmx/laravel-pay)

---
Laravel Pay

1. 
---

Laravel Pay description.

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

This is the contents of the published config file:

```php
return [
];
```

## Configuration

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

## Usage


```php
$skeleton = new VendorName\Skeleton();
echo $skeleton->echoPhrase('Hello, VendorName!');
```

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