<?php

use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Tests\Fixtures\User;

test('a customer can be created', function () {
    $user = User::factory()->create();

    expect($user->service)->toBeNull()
        ->and($user->service_customer_id)->toBeNull();

    $customer = $user->createCustomerWithDriver();

    $user = $user->fresh();
    expect($user->service)->not->toBeNull()
        ->and($user->service_customer_id)->not->toBeNull()
        ->and($customer)
        ->toBeObject()
        ->toBeInstanceOf(Customer::class);
});

test('a customer can be requested as customer driver', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

    expect($user->asCustomer())
        ->toBeObject()
        ->toBeInstanceOf(Customer::class);
});

test('a customer can request a customer driver it self', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

    expect($user->asCustomer()->asDriver())
        ->toBeObject()
        ->not->toBeInstanceOf(Customer::class);
});

test('a customer anonymous can retive a customer object', function () {
    $user = User::factory()->make();
    expect(User::all())
        ->toHaveCount(0)
        ->and($user->asAnonymousCustomer())->toBeObject()
        ->toBeInstanceOf(Customer::class);
});
