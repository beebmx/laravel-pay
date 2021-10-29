<?php

namespace Beebmx\LaravelPay\Tests\Feature;

use Beebmx\LaravelPay\Address;
use Beebmx\LaravelPay\Tests\FeatureTestCase;
use Beebmx\LaravelPay\Tests\Fixtures\User;

class AddressTest extends FeatureTestCase
{
    /** @test */
    public function an_address_can_be_set_for_shipping()
    {
        $user = User::factory()->hasAddresses(4)->create();
        $user->load('addresses');

        $this->assertNull($user->currentShippingAddress());

        $user->shipping($user->addresses->first());

        $this->assertNotNull($user->currentShippingAddress());
        $this->assertEquals($user->addresses->first(), $user->currentShippingAddress());
    }

    /** @test */
    public function it_validates_if_has_an_address_for_shipping()
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();

        $this->assertFalse($user->hasShipping());

        $user->shipping($address);
        $this->assertTrue($user->hasShipping());
    }
}
