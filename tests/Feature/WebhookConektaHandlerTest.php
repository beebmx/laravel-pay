<?php

use Beebmx\LaravelPay\Http\Webhooks\ConektaHandler;
use Beebmx\LaravelPay\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['pay.default' => 'conekta']);
    config(['app.url' => 'http://laravel-pay.test']);
});

test('an order paid mark as paid the transaction', function () {
    $id = 'ord_'.Str::random(17);
    $transaction = Transaction::factory()->create(['service_payment_id' => $id, 'status' => 'pending']);

    (new ConektaHandler)->handleOrderPaid(mockConektaResponse($id));

    expect($transaction->fresh()->status)
        ->toEqual('paid');
});

test('an order canceled mark as cancel the transaction', function () {
    $id = 'ord_'.Str::random(17);
    $transaction = Transaction::factory()->create(['service_payment_id' => $id, 'status' => 'paid']);

    (new ConektaHandler)->handleOrderCanceled(mockConektaResponse($id));

    expect($transaction->fresh()->status)
        ->toEqual('canceled');
});

function mockConektaResponse(string $id, string $status = 'paid'): array
{
    $cus = 'cus_'.Str::random(17);

    return [
        'livemode' => false,
        'amount' => 50000,
        'currency' => 'USD',
        'payment_status' => $status,
        'amount_refunded' => 0,
        'customer_info' => [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'corporate' => false,
            'customer_id' => $cus,
            'object' => 'customer_info',
        ],
        'object' => 'order',
        'id' => $id,
        'metadata' => [],
        'created_at' => Carbon::now()->timestamp,
        'updated_at' => Carbon::now()->timestamp,
        'line_items' => [
            'object' => 'list',
            'has_more' => false,
            'total' => 1,
            'data' => [
                [
                    'name' => 'Testing product',
                    'unit_price' => 50000,
                    'quantity' => 1,
                    'object' => 'line_item',
                    'id' => 'line_item_'.Str::random(17),
                    'parent_id' => $id,
                    'metadata' => [],
                    'antifraud_info' => [],
                ],
            ],
        ],
        'charges' => [
            'object' => 'list',
            'has_more' => false,
            'total' => 1,
            'data' => [
                [
                    'id' => Str::random(24),
                    'livemode' => false,
                    'created_at' => Carbon::now()->timestamp,
                    'currency' => 'USD',
                    'device_fingerprint' => Str::random(30),
                    'payment_method' => [
                        'name' => 'John Doe',
                        'exp_month' => '12',
                        'exp_year' => '22',
                        'auth_code' => '012345',
                        'object' => 'card_payment',
                        'type' => 'credit',
                        'last4' => '4242',
                        'brand' => 'visa',
                        'issuer' => 'bank_brand',
                        'account_type' => 'BANK',
                        'country' => 'US',
                        'fraud_indicators' => [],
                    ],
                    'object' => 'charge',
                    'description' => 'Payment from order',
                    'status' => $status,
                    'amount' => 50000,
                    'paid_at' => Carbon::now()->timestamp,
                    'fee' => 1972,
                    'customer_id' => $cus,
                    'order_id' => $id,
                ],
            ],
        ],

    ];
}
