<?php

use Beebmx\LaravelPay\Address;
use Beebmx\LaravelPay\Tests\Fixtures\User;

it('returns the address as string', function () {
    $address = Address::factory()->create(['line2' => 'Apartment 1']);

    expect($address->asString())
        ->not->toBeNull()
        ->toContain('Apartment 1');
});

it('returns the address as html', function () {
    $address = Address::factory()->create();

    expect($address->asHtml())
        ->not->toBeNull()
        ->toContain('<br />');
});

test('an address can be set for shipping', function () {
    $user = User::factory()->hasAddresses(4)->create();
    $user->load('addresses');

    expect($user->currentShippingAddress())
        ->toBeNull();

    $user->shipping($user->addresses->first());

    expect($user->currentShippingAddress())
        ->not->toBeNull()
        ->toEqual($user->addresses->first());
});

it('validates if has an address for shipping', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();

    expect($user->hasShipping())
        ->toBeFalse();

    $user->shipping($address);
    expect($user->hasShipping())
        ->toBeTrue();
});
