<?php

namespace Beebmx\LaravelPay\Tests\Unit;

use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class ElementTest extends TestCase
{
    /** @test */
    public function it_returns_an_array()
    {
        $user = new User;
        $customer = new Customer($user, $this->mock_object());

        $array = $customer->toArray();

        $this->assertIsObject($customer);
        $this->assertIsArray($array);
        $this->assertArrayHasKey('object', $array);
        $this->assertEquals('customer', $array['object']);
    }

    /** @test */
    public function it_returns_a_json()
    {
        $user = new User;
        $customer = new Customer($user, $this->mock_object());

        $json = $customer->toJson();

        $this->assertIsObject($customer);
        $this->assertJson($json);
    }

    /** @test */
    public function it_can_access_to_object_properties()
    {
        $user = new User;
        $customer = new Customer($user, $this->mock_object());

        $this->assertSame('customer', $customer->object);
        $this->assertSame('mxn', $customer->currency);
        $this->assertSame('John Doe', $customer->name);
    }

    protected function mock_object(): object
    {
        return (object)[
            'id' => 'cus_' . Str::random(14),
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
                'footer' => null
            ],
            'livemode' => false,
            'name' => 'John Doe',
            'phone' => null,
            'preferred_locales' => [],
            'shipping' => null,
        ];
    }
}
