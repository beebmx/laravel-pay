<?php

use Beebmx\LaravelPay\Http\Webhooks\StripeHandler;
use Beebmx\LaravelPay\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['pay.default' => 'stripe']);
    config(['app.url' => 'http://laravel-pay.test']);
});

test('a payment intent succeeded mark as paid the transaction', function () {
    $id = 'pi_'.Str::random(17);
    $transaction = Transaction::factory()->create(['service_payment_id' => $id, 'status' => 'pending']);

    (new StripeHandler)->handlePaymentIntentSucceeded(mockStripeResponse($id));

    expect($transaction->fresh()->status)
        ->toEqual('paid');
});

test('a payment intent canceled mark as cancel the transaction', function () {
    $id = 'pi_'.Str::random(17);
    $transaction = Transaction::factory()->create(['service_payment_id' => $id, 'status' => 'pending']);

    (new StripeHandler)->handlePaymentIntentSucceeded(mockStripeResponse($id, 'canceled', 'payment_intent.canceled'));

    expect($transaction->fresh()->status)
        ->toEqual('canceled');
});

function mockStripeResponse(string $id, string $status = 'succeeded', string $type = 'payment_intent.succeeded')
{
    return [
        'created' => Carbon::now()->timestamp,
        'livemode' => false,
        'id' => 'evt_'.Str::random(14),
        'type' => $type,
        'object' => 'event',
        'request' => null,
        'pending_webhooks' => 1,
        'api_version' => '2020-03-02',
        'data' => [
            'object' => [
                'id' => $id,
                'object' => 'payment_intent',
                'amount' => 50000,
                'amount_capturable' => 0,
                'amount_received' => 50000,
                'application' => null,
                'application_fee_amount' => null,
                'automatic_payment_methods' => null,
                'canceled_at' => null,
                'cancellation_reason' => null,
                'capture_method' => 'automatic',
                'charges' => [
                    'object' => 'list',
                    'data' => [
                        [
                            'id' => 'ch_'.Str::random(14),
                            'object' => 'charge',
                            'amount' => 50000,
                            'amount_captured' => 50000,
                            'amount_refunded' => 0,
                            'application' => null,
                            'application_fee' => null,
                            'application_fee_amount' => null,
                            'balance_transaction' => 'txn_'.Str::random(14),
                            'billing_details' => [
                                'address' => [
                                    'city' => null,
                                    'country' => null,
                                    'line1' => null,
                                    'line2' => null,
                                    'postal_code' => null,
                                    'state' => null,
                                ],
                                'email' => null,
                                'name' => 'John Doe',
                                'phone' => null,
                            ],
                            'calculated_statement_descriptor' => 'Descriptor',
                            'captured' => true,
                            'created' => Carbon::now()->timestamp,
                            'currency' => 'usd',
                            'customer' => null,
                            'description' => null,
                            'disputed' => false,
                            'failure_code' => null,
                            'failure_message' => null,
                            'fraud_details' => [],
                            'invoice' => null,
                            'livemode' => false,
                            'metadata' => [],
                            'on_behalf_of' => null,
                            'order' => null,
                            'outcome' => [
                                'network_status' => 'approved_by_network',
                                'reason' => null,
                                'risk_level' => 'normal',
                                'risk_score' => 42,
                                'seller_message' => 'Payment complete.',
                                'type' => 'authorized',
                            ],
                            'paid' => true,
                            'payment_intent' => $id,
                            'payment_method' => 'pm_00000000000000',
                            'payment_method_details' => [
                                'card' => [
                                    'brand' => 'visa',
                                    'checks' => [
                                        'address_line1_check' => null,
                                        'address_postal_code_check' => null,
                                        'cvc_check' => 'pass',
                                    ],
                                    'country' => 'US',
                                    'exp_month' => 12,
                                    'exp_year' => 2023,
                                    'fingerprint' => Str::random(16),
                                    'funding' => 'credit',
                                    'installments' => null,
                                    'last4' => '4242',
                                    'network' => 'visa',
                                    'three_d_secure' => null,
                                    'wallet' => null,
                                ],
                                'type' => 'card',
                            ],
                            'receipt_email' => null,
                            'receipt_number' => null,
                            'receipt_url' => 'https://pay.stripe.com/receipts/acct_'.Str::random(16).'/ch_'.Str::random(24).'/rcpt_'.Str::random(31),
                            'refunded' => false,
                            'refunds' => [
                                'object' => 'list',
                                'data' => [
                                ],
                                'has_more' => false,
                                'url' => '/v1/charges/ch_'.Str::random(24).'/refunds',
                            ],
                            'review' => null,
                            'shipping' => null,
                            'source_transfer' => null,
                            'statement_descriptor' => null,
                            'statement_descriptor_suffix' => null,
                            'status' => $status,
                            'transfer_data' => null,
                            'transfer_group' => null,
                        ],
                    ],
                    'has_more' => false,
                    'url' => '/v1/charges?payment_intent=pi_'.Str::random(24),
                ],
                'client_secret' => 'pi_'.Str::random(25).'_secret_'.Str::random(25),
                'confirmation_method' => 'automatic',
                'created' => Carbon::now()->timestamp,
                'currency' => 'usd',
                'customer' => null,
                'description' => null,
                'invoice' => null,
                'last_payment_error' => null,
                'livemode' => false,
                'metadata' => [],
                'next_action' => null,
                'on_behalf_of' => null,
                'payment_method' => 'pm_'.Str::random(14),
                'payment_method_options' => [
                    'card' => [
                        'installments' => null,
                        'network' => null,
                        'request_three_d_secure' => 'automatic',
                    ],
                ],
                'payment_method_types' => [
                    'card',
                ],
                'processing' => null,
                'receipt_email' => null,
                'review' => null,
                'setup_future_usage' => null,
                'shipping' => null,
                'statement_descriptor' => null,
                'statement_descriptor_suffix' => null,
                'status' => $status,
                'transfer_data' => null,
                'transfer_group' => null,
            ],
        ],
    ];
}
