<?php

use Beebmx\LaravelPay\Drivers\ConektaDriver;
use Beebmx\LaravelPay\Drivers\SandboxDriver;
use Beebmx\LaravelPay\Drivers\StripeDriver;
use Beebmx\LaravelPay\Exceptions\DriverNonInstantiable;
use Beebmx\LaravelPay\Exceptions\InvalidDriver;
use Beebmx\LaravelPay\Factory;
use Beebmx\LaravelPay\Tests\Fixtures\User;

test('a factory class exists', function () {
    expect(class_exists(Factory::class))
        ->toBeTrue();
});

it('returns the default driver', function () {
    config(['pay.default' => 'sandbox']);

    expect(Factory::getService())
        ->toEqual(SandboxDriver::class);
});

it('throws an exception when default driver is invalid', function () {
    config(['pay.default' => 'invalid']);

    Factory::getService();
})->throws(InvalidDriver::class);

it('throws an exception when driver has not driver class', function () {
    config(['pay.default' => 'error']);
    config(['pay.drivers' => ['error' => ['driver']]]);

    Factory::make();
})->throws(DriverNonInstantiable::class);

it('returns an instance of different default driver', function () {
    expect(Factory::make('stripe'))
        ->toEqual(StripeDriver::class);
});

it('returns all the driver classes available', function () {
    expect(Factory::getDriversClasses())->toHaveCount(3)
        ->and(Factory::getDriversClasses()['sandbox'])->toEqual(SandboxDriver::class)
        ->and(Factory::getDriversClasses()['stripe'])->toEqual(StripeDriver::class)
        ->and(Factory::getDriversClasses()['conekta'])->toEqual(ConektaDriver::class);
});

it('finds the right driver for a customer', function () {
    $user = new User;
    $user->service = 'stripe';

    expect($user->getCustomerDriver())
        ->toEqual(StripeDriver::class);
});

it('throws an exception if request a driver without be setting before', function () {
    $user = new User;

    $user->getCustomerDriver();
})->throws(InvalidDriver::class);
