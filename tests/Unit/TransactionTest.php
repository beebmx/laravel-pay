<?php

namespace Beebmx\LaravelPay\Tests\Unit;

use Beebmx\LaravelPay\Tests\FeatureTestCase;
use Beebmx\LaravelPay\Transaction;
use Illuminate\Support\Str;

class TransactionTest extends FeatureTestCase
{
    /** @test */
    public function it_change_transaction_status_to_paid()
    {
        $transaction = Transaction::factory()->create(['status' => 'pending']);

        $transaction->paid();

        $this->assertEquals('paid', $transaction->fresh()->status);
    }

    /** @test */
    public function it_change_transaction_status_to_canceled()
    {
        $transaction = Transaction::factory()->create(['status' => 'paid']);

        $transaction->canceled();

        $this->assertEquals('canceled', $transaction->fresh()->status);
    }

    /** @test */
    public function it_change_transaction_status_with_any_string()
    {
        $transaction = Transaction::factory()->create(['status' => 'pending']);

        $transaction->changeStatus('succeeded');

        $this->assertEquals('succeeded', $transaction->fresh()->status);
    }

    /** @test */
    public function it_find_a_transaction_and_returns_it()
    {
        $payment_id = 'ord_' . Str::random(17);
        $transaction = Transaction::factory()->create(['service_payment_id' => $payment_id]);

        $this->assertEquals($transaction->fresh(), Transaction::findByPaymentId($payment_id));
        $this->assertNull(Transaction::findByPaymentId('ord_' . Str::random(17)));
    }
}
