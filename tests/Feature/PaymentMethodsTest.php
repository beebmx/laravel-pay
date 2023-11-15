<?php

use Beebmx\LaravelPay\Elements\PaymentMethod;
use Beebmx\LaravelPay\Tests\Fixtures\User;

test('a customer can add a payment method', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);
    $paymentMethod = $user->addPaymentMethod('src_0123456789');

    expect($paymentMethod)
        ->toBeObject();
});

test('a customer has a default a payment method after added a payment method', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

    expect($user->hasDefaultPayment())->toBeFalse();

    $paymentMethod = $user->addPaymentMethod('token_123');

    expect($user->hasDefaultPayment())->toBeTrue()
        ->and($user->service_payment_id)->not->toBeNull()
        ->and($user->service_payment_type)->not->toBeNull()
        ->and($user->service_payment_brand)->not->toBeNull()
        ->and($user->service_payment_last_four)->not->toBeNull()
        ->and($user->service_payment_id)->toEqual($paymentMethod->id);
});

test('a customer can find a payment by id', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

    expect($user->findPaymentMethod('src_987654310'))
        ->not->toBeNull()
        ->toBeObject()
        ->toBeInstanceOf(PaymentMethod::class)
        ->and($user->findPaymentMethod('src_987654310')->id)->toEqual('src_987654310');
});

test('a customer can retrive their default payment method', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);
    expect($user->getDefaultPayment())
        ->toBeNull();

    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

    expect($user->getDefaultPayment())
        ->not->toBeNull()
        ->toBeObject()
        ->and($user->findPaymentMethod('src_0123456789')->id)->toEqual('src_0123456789');
});

test('a one time payment method exists or not', function () {
    $user = User::factory()->create();
    expect($user->hasOneTimePaymentMethod())
        ->toBeFalse();

    $user->token('token_123');
    expect($user->hasOneTimePaymentMethod())
        ->toBeTrue();
});

test('an one time token payment method is returned if exists', function () {
    $user = User::factory()->create();
    expect($user->getOneTimeTokenPaymentMethod())
        ->toBeNull();

    $user->token('token_123');

    expect($user->getOneTimeTokenPaymentMethod())
        ->toBeInstanceOf(PaymentMethod::class);
});

test('an oxxo payment method is returned if exists', function () {
    $user = User::factory()->create();
    expect($user->getOxxoPaymentMethod())
        ->toBeNull();

    $user->oxxo();

    expect($user->getOxxoPaymentMethod())
        ->toBeInstanceOf(PaymentMethod::class);
});
