<?php

namespace Beebmx\LaravelPay\Tests\Unit;

use Beebmx\LaravelPay\Exceptions\CustomerAlreadyExists;
use Beebmx\LaravelPay\Exceptions\InvalidCustomer;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    /** @test */
    public function it_defines_if_a_user_is_set_as_customer()
    {
        $user = new User;
        $this->assertFalse($user->isCustomer());

        $user = new User;
        $user->service_customer_id = 'customer_123';
        $this->assertTrue($user->isCustomer());
    }

    /** @test */
    public function it_throws_an_exception_if_requires_a_customer_driver_with_an_invalid_user()
    {
        $user = new User;

        $this->expectException(InvalidCustomer::class);
        $user->asCustomer();
    }

    /** @test */
    public function it_throws_an_exception_if_a_customer_tries_to_create_an_already_customer()
    {
        $user = new User;
        $user->service_customer_id = 'customer_123';

        $this->expectException(CustomerAlreadyExists::class);
        $user->createCustomerWithDriver();
    }
}
