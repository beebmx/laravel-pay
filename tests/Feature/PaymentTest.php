<?php

namespace Beebmx\LaravelPay\Tests\Feature;

use Beebmx\LaravelPay\Elements\Payment;
use Beebmx\LaravelPay\Exceptions\InvalidPayment;
use Beebmx\LaravelPay\Exceptions\InvalidPaymentMethod;
use Beebmx\LaravelPay\Tests\FeatureTestCase;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Beebmx\LaravelPay\Transaction;
use Beebmx\LaravelPay\TransactionItem;

class PaymentTest extends FeatureTestCase
{
    /** @test */
    public function a_charge_requires_at_least_one_product()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

        $this->expectException(InvalidPayment::class);
        $user->charge([]);
    }

    /** @test */
    public function it_requires_a_payment_method_defined_to_perform_a_payment()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

        $this->expectException(InvalidPaymentMethod::class);
        $user->charge([['name' => 'Product 01', 'price' => 500]]);
    }

    /** @test */
    public function a_charge_requires_at_least_price_in_product()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

        $this->expectException(InvalidPayment::class);
        $user->charge(['name' => 'Product name']);
    }

    /** @test */
    public function a_charge_with_many_products_requires_always_prices_key()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

        $this->expectException(InvalidPayment::class);
        $user->charge([['name' => 'Product 01', 'price' => 500], ['name' => 'Product 02']]);
    }

    /** @test */
    public function a_charge_performs_a_payment()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

        $payment = $user->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
    }

    /** @test */
    public function a_charge_save_the_transaction()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

        $this->assertCount(0, Transaction::all());

        $user->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

        $this->assertCount(1, Transaction::all());
    }

    /** @test */
    public function a_charge_save_the_transaction_items()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

        $this->assertCount(0, TransactionItem::all());

        $user->charge([[
            'name' => 'Product 01',
            'price' => 100,
        ], [
            'name' => 'Product 02',
            'price' => 200,
        ]]);

        $this->assertCount(2, TransactionItem::all());
    }

    /** @test */
    public function a_charge_saved_can_return_the_transaction()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

        $payment = $user->charge([
            'name' => 'Product 01',
            'price' => 100,
        ]);

        $this->assertInstanceOf(Transaction::class, $payment->getTransaction());
        $this->assertNotNull($payment->getTransaction()->id);
        $this->assertNotNull('src_0123456789', $payment->getTransaction()->service_id);
    }

    /** @test */
    public function a_charge_payment_can_have_shipping()
    {
        $user = User::factory()->hasAddresses()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);
        $address = $user->addresses()->first();

        $payment = $user
            ->shipping($address)
            ->charge([
                'name' => 'Testing product',
                'price' => 500,
            ]);
        $this->assertNotNull($payment->getTransaction()->address_id);
        $this->assertNotNull($payment->getTransaction()->shipping);
        $this->assertEquals($address->id, $payment->getTransaction()->address_id);

        $payment = $user->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);
        $this->assertNull($payment->getTransaction()->address_id);
        $this->assertNull($payment->getTransaction()->shipping);
    }

    /** @test */
    public function a_charge_payment_can_have_discount()
    {
        $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

        $payment = $user
            ->discount(200)
            ->charge([
                'name' => 'Testing product',
                'price' => 500,
            ]);

        $this->assertNotNull($payment->getTransaction()->total);
        $this->assertNotNull($payment->getTransaction()->discount);
        $this->assertEquals(300, $payment->getTransaction()->amount);
        $this->assertEquals(500, $payment->getTransaction()->total);
        $this->assertEquals(200, $payment->getTransaction()->discount);
    }

    /** @test */
    public function a_user_can_create_a_charge_with_one_time_payment_method()
    {
        $user = User::factory()->create();

        $this->assertCount(0, Transaction::all());

        $user
            ->token('tok_visa4242')
            ->charge([
                'name' => 'Testing product',
                'price' => 500,
            ]);

        $this->assertCount(1, Transaction::all());
    }

    /** @test */
    public function a_user_can_create_a_charge_with_oxxo_payment()
    {
        $user = User::factory()->create();

        $this->assertCount(0, Transaction::all());

        $user
            ->oxxo()
            ->charge([
                'name' => 'Testing product',
                'price' => 500,
            ]);

        $this->assertCount(1, Transaction::all());
    }
}
