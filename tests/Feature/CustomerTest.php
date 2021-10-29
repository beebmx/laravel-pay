<?php

namespace Beebmx\LaravelPay\Tests\Feature;

use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Tests\FeatureTestCase;
use Beebmx\LaravelPay\Tests\Fixtures\User;

class CustomerTest extends FeatureTestCase
{
    /** @test */
    public function a_customer_can_be_created()
    {
        $user = User::factory()->create();

        $this->assertNull($user->service);
        $this->assertNull($user->service_customer_id);

        $customer = $user->createCustomerWithDriver();

        $user = $user->fresh();
        $this->assertNotNull($user->service);
        $this->assertNotNull($user->service_customer_id);
        $this->assertIsObject($customer);
        $this->assertInstanceOf(Customer::class, $customer);
    }

    /** @test */
    public function a_customer_can_be_requested_as_customer_driver()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

        $this->assertIsObject($user->asCustomer());
        $this->assertInstanceOf(Customer::class, $user->asCustomer());
    }

    /** @test */
    public function a_customer_can_request_a_customer_driver_it_self()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

        $this->assertIsObject($user->asCustomer()->asDriver());
        $this->assertNotInstanceOf(Customer::class, $user->asCustomer()->asDriver());
    }

    /** @test */
    public function a_customer_anonymous_can_retive_a_customer_object()
    {
        $user = User::factory()->make();
        $this->assertCount(0, User::all());

        $this->assertIsObject($user->asAnonymousCustomer());
        $this->assertInstanceOf(Customer::class, $user->asAnonymousCustomer());
    }
}
