<?php

namespace Beebmx\LaravelPay\Tests\Unit;

use Beebmx\LaravelPay\Address;
use Beebmx\LaravelPay\Tests\FeatureTestCase;

class AddressTest extends FeatureTestCase
{
    /** @test */
    public function it_returns_the_address_as_string()
    {
        $address = Address::factory()->create(['line2' => 'Apartment 1']);

        $this->assertNotNull($address->asString());
        $this->assertStringContainsString('Apartment 1', $address->asString());
    }

    /** @test */
    public function it_returns_the_address_as_html()
    {
        $address = Address::factory()->create();

        $this->assertNotNull($address->asHtml());
        $this->assertStringContainsString('<br />', $address->asHtml());
    }
}
