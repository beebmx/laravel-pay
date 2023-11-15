<?php

use Beebmx\LaravelPay\Exceptions\CustomerAlreadyExists;
use Beebmx\LaravelPay\Exceptions\InvalidCustomer;
use Beebmx\LaravelPay\Tests\Fixtures\User;

it('defines if a user is set as customer', function () {
    $user = new User;
    expect($user->isCustomer())
        ->toBeFalse();

    $user = new User;
    $user->service_customer_id = 'customer_123';
    expect($user->isCustomer())
        ->toBeTrue();
});

it('throws an exception if requires a customer driver with an invalid user', function () {
    $user = new User;

    $user->asCustomer();
})->throws(InvalidCustomer::class);

it('throws an exception if a customer tries to create an already customer', function () {
    $user = new User;
    $user->service_customer_id = 'customer_123';

    $user->createCustomerWithDriver();
})->throws(CustomerAlreadyExists::class);
