<?php

use Beebmx\LaravelPay\Exceptions\InvalidCustomer;
use Beebmx\LaravelPay\Tests\Fixtures\User;

it('defines if a user has default payment', function () {
    $user = new User;
    expect($user->hasDefaultPayment())
        ->toBeFalse();

    $user = new User;
    $user->service_payment_id = 'payment_123';
    expect($user->hasDefaultPayment())
        ->toBeTrue();
});

it('throw an exception if a invalid user adds a payment method', function () {
    $user = new User;

    $user->addPaymentMethod('token_123');
})->throws(InvalidCustomer::class);
