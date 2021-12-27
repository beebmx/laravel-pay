<?php

namespace Beebmx\LaravelPay\Tests\Feature;

use Beebmx\LaravelPay\Elements\PaymentMethod;
use Beebmx\LaravelPay\Tests\FeatureTestCase;
use Beebmx\LaravelPay\Tests\Fixtures\User;

class PaymentMethodsTest extends FeatureTestCase
{
    /** @test */
    public function a_customer_can_add_a_payment_method()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);
        $paymentMethod = $user->addPaymentMethod('src_0123456789');

        $this->assertIsObject($paymentMethod);
    }

    /** @test */
    public function a_customer_has_a_default_a_payment_method_after_added_a_payment_method()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

        $this->assertFalse($user->hasDefaultPayment());

        $paymentMethod = $user->addPaymentMethod('token_123');
        $this->assertTrue($user->hasDefaultPayment());
        $this->assertNotNull($user->service_payment_id);
        $this->assertNotNull($user->service_payment_type);
        $this->assertNotNull($user->service_payment_brand);
        $this->assertNotNull($user->service_payment_last_four);
        $this->assertEquals($paymentMethod->id, $user->service_payment_id);
    }

    /** @test */
    public function a_customer_can_find_a_payment_by_id()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

        $this->assertNotNull($user->findPaymentMethod('src_987654310'));
        $this->assertIsObject($user->findPaymentMethod('src_987654310'));
        $this->assertInstanceOf(PaymentMethod::class, $user->findPaymentMethod('src_987654310'));
        $this->assertEquals('src_987654310', $user->findPaymentMethod('src_987654310')->id);
    }

    /** @test */
    public function a_customer_can_retrive_their_default_payment_method()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);
        $this->assertNull($user->getDefaultPayment());

        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);
        $this->assertNotNull($user->getDefaultPayment());
        $this->assertIsObject($user->getDefaultPayment());
        $this->assertEquals('src_0123456789', $user->findPaymentMethod('src_0123456789')->id);
    }

    /** @test */
    public function a_one_time_payment_method_exists_or_not()
    {
        $user = new User;
        $this->assertFalse($user->hasOneTimePaymentMethod());

        $user->token('token_123');
        $this->assertTrue($user->hasOneTimePaymentMethod());
    }

    /** @test */
    public function an_one_time_token_payment_method_is_returned_if_exists()
    {
        $user = new User;
        $this->assertNull($user->getOneTimeTokenPaymentMethod());

        $user->token('token_123');
        $this->assertInstanceOf(PaymentMethod::class, $user->getOneTimeTokenPaymentMethod());
    }

    /** @test */
    public function an_oxxo_payment_method_is_returned_if_exists()
    {
        $user = new User;
        $this->assertNull($user->getOxxoPaymentMethod());

        $user->oxxo();
        $this->assertInstanceOf(PaymentMethod::class, $user->getOxxoPaymentMethod());
    }
}
