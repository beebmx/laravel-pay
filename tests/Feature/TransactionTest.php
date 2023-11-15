<?php

use Beebmx\LaravelPay\Transaction;
use Illuminate\Support\Str;

it('change transaction status to paid', function () {
    $transaction = Transaction::factory()->create(['status' => 'pending']);

    $transaction->paid();

    expect($transaction->fresh()->status)
        ->toEqual('paid');
});

it('change transaction status to canceled', function () {
    $transaction = Transaction::factory()->create(['status' => 'paid']);

    $transaction->canceled();

    expect($transaction->fresh()->status)
        ->toEqual('canceled');
});

it('change transaction status with any string', function () {
    $transaction = Transaction::factory()->create(['status' => 'pending']);

    $transaction->changeStatus('succeeded');

    expect($transaction->fresh()->status)
        ->toEqual('succeeded');
});

it('find a transaction and returns it', function () {
    $payment_id = 'ord_'.Str::random(17);
    $transaction = Transaction::factory()->create(['service_payment_id' => $payment_id]);

    expect(Transaction::findByPaymentId($payment_id))->toEqual($transaction->fresh())
        ->and(Transaction::findByPaymentId('ord_'.Str::random(17)))->toBeNull();
});
