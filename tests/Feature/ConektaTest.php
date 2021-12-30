<?php

namespace Beebmx\LaravelPay\Tests\Feature;

use Beebmx\LaravelPay\Address;
use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Elements\PaymentMethod;
use Beebmx\LaravelPay\Tests\FeatureTestCase;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Beebmx\LaravelPay\Transaction;
use Beebmx\LaravelPay\TransactionItem;

class ConektaTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        if ((getenv('PAY_TEST_FULL_SUITE') === '(false)' || !getenv('PAY_TEST_FULL_SUITE')) || !getenv('CONEKTA_SECRET_KEY')) {
            $this->markTestSkipped('Conekta Test are skipped');
        }

        parent::setUp();
    }

    /** @test */
    public function a_customer_can_be_created()
    {
        $user = User::factory()->create();

        $customer = $user->createCustomerWithDriver();

        $this->assertNotNull($user->service);
        $this->assertEquals('conekta', $user->service);
        $this->assertNotNull($user->service_customer_id);
        $this->assertIsObject($customer);
    }

    /** @test */
    public function a_customer_can_be_requested_as_customer_driver()
    {
        $user = User::factory()->create();
        $user->createCustomerWithDriver();
        $customer = $user->fresh()->asCustomer();

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertIsObject($customer);
    }

    /** @test */
    public function a_customer_can_add_a_payment_method()
    {
        $user = User::factory()->create();
        $user->createCustomerWithDriver();

        $paymentMethod = $user->addPaymentMethod('tok_test_visa_4242');

        $this->assertIsObject($paymentMethod);
    }

    /** @test */
    public function a_customer_can_retrive_their_default_payment_method()
    {
        $user = User::factory()->create();
        $user->createCustomerWithDriver();
        $user->addPaymentMethod('tok_test_visa_4242');

        $paymentMethod = $user->getDefaultPayment();
        $user = $user->fresh();

        $this->assertNotNull($paymentMethod);
        $this->assertIsObject($paymentMethod);
        $this->assertInstanceOf(PaymentMethod::class, $paymentMethod);
        $this->assertEquals($user->service_payment_id, $paymentMethod->id);
    }

    /** @test */
    public function a_charge_save_the_transaction_and_items()
    {
        $user = User::factory()->create();
        $user->createCustomerWithDriver();
        $user->addPaymentMethod('tok_test_visa_4242');

        $this->assertCount(0, Transaction::all());
        $this->assertCount(0, TransactionItem::all());

        $user->charge([[
            'name' => 'Product 01',
            'price' => 100
        ], [
            'name' => 'Product 02',
            'price' => 200
        ]]);

        $this->assertCount(1, Transaction::all());
        $this->assertCount(2, TransactionItem::all());
    }

    /** @test */
    public function a_charge_save_the_address()
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $user->createCustomerWithDriver();
        $user->addPaymentMethod('tok_test_visa_4242');

        $payment = $user
            ->shipping($address)
            ->charge(['name' => 'Product 01', 'price' => 500]);

        $this->assertArrayHasKey('shipping_contact', $payment->asDriver());
    }

    /** @test */
    public function a_user_can_create_a_charge_with_one_time_token_payment_method()
    {
        $user = User::factory()->create();
        $user->createCustomerWithDriver();

        $this->assertCount(0, Transaction::all());

        $user
            ->token('tok_test_visa_4242')
            ->charge([
                'name' => 'Testing product',
                'price' => 500,
            ]);

        $this->assertCount(1, Transaction::all());
    }

    /** @test */
    public function an_anonymous_user_can_create_a_charge_with_one_time_token_payment_method()
    {
        $user = User::factory()->create();
        $user->phone = '+520000000000';

        $this->assertCount(0, Transaction::all());

        $user
            ->token('tok_test_visa_4242')
            ->charge([
                'name' => 'Testing product',
                'price' => 500,
            ]);

        $this->assertCount(1, Transaction::all());
    }

    /** @test */
    public function a_user_can_create_a_charge_with_oxxo_payment_method()
    {
        $user = User::factory()->create();
        $user->createCustomerWithDriver();
        $user->phone = '+520000000000';

        $this->assertCount(0, Transaction::all());

        $user
            ->oxxo()
            ->charge([
                'name' => 'Testing product',
                'price' => 500,
            ]);

        $this->assertCount(1, Transaction::all());
    }

    /** @test */
    public function an_anonymous_user_can_create_a_charge_with_oxxo_payment_method()
    {
        $user = User::factory()->create();
        $user->phone = '+520000000000';

        $this->assertCount(0, Transaction::all());

        $user
            ->oxxo()
            ->charge([
                'name' => 'Testing product',
                'price' => 500,
            ]);

        $this->assertCount(1, Transaction::all());
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('pay.default', 'conekta');
        $app['config']->set('pay.currency', 'mxn');
        $app['config']->set('pay.oxxo.days_to_expire', 1);
    }
}
