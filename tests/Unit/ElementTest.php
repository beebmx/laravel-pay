<?php

use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

it('returns an array', function () {
    $user = new User;
    $customer = new Customer($user, mockElementObject());

    $array = $customer->toArray();

    expect($customer)->toBeObject()
        ->and($array['object'])->toEqual('customer')
        ->and($array)
        ->toBeArray()
        ->toHaveKey('object');
});

it('returns a json', function () {
    $user = new User;
    $customer = new Customer($user, mockElementObject());

    $json = $customer->toJson();

    expect($customer)->toBeObject()
        ->and($json)->toBeJson();
});

it('can access to object properties', function () {
    $user = new User;
    $customer = new Customer($user, mockElementObject());

    expect($customer->object)->toBe('customer')
        ->and($customer->currency)->toBe('mxn')
        ->and($customer->name)->toBe('John Doe');
});

function mockElementObject(): object
{
    return (object) [
        'id' => 'cus_'.Str::random(14),
        'object' => 'customer',
        'address' => null,
        'balance' => 0,
        'created' => Carbon::now()->timestamp,
        'currency' => 'mxn',
        'default_source' => 'card_18NVYR2eZvKYlo2CQ2ieV9S5',
        'delinquent' => false,
        'description' => 'Mock name',
        'discount' => null,
        'email' => 'john@doe.co',
        'invoice_prefix' => Str::random(8),
        'invoice_settings' => [
            'custom_fields' => null,
            'default_payment_method' => null,
            'footer' => null,
        ],
        'livemode' => false,
        'name' => 'John Doe',
        'phone' => null,
        'preferred_locales' => [],
        'shipping' => null,
    ];
}
