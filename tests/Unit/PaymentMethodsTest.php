<?php

namespace Beebmx\LaravelPay\Tests\Unit;

use Beebmx\LaravelPay\Exceptions\InvalidCustomer;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase;

class PaymentMethodsTest extends TestCase
{
    /** @test */
    public function it_defines_if_a_user_has_default_payment()
    {
        $user = new User;
        $this->assertFalse($user->hasDefaultPayment());

        $user = new User;
        $user->service_payment_id = 'payment_123';
        $this->assertTrue($user->hasDefaultPayment());
    }

    /** @test */
    public function it_throw_an_exception_if_a_invalid_user_adds_a_payment_method()
    {
        $user = new User;

        $this->expectException(InvalidCustomer::class);
        $user->addPaymentMethod('token_123');
    }
}
