<?php

use Beebmx\LaravelPay\Address;
use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Elements\PaymentMethod;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Beebmx\LaravelPay\Transaction;
use Beebmx\LaravelPay\TransactionItem;

beforeEach(function () {
    config(['pay.default' => 'stripe']);
    config(['pay.currency' => 'mxn']);
    config(['pay.oxxo.days_to_expire' => 1]);
})->skip(
    (getenv('PAY_TEST_FULL_SUITE') === '(false)' || ! getenv('PAY_TEST_FULL_SUITE')) || ! getenv('STRIPE_SECRET_KEY'),
    'Stripe Test are skipped'
);

test('a customer can be created', function () {
    $user = User::factory()->create();

    $customer = $user->createCustomerWithDriver();

    expect($user->service)->not->toBeNull()
        ->toEqual('stripe')
        ->and($user->service_customer_id)->not->toBeNull()
        ->and($customer)->toBeObject();
});

test('a customer can be requested as customer driver', function () {
    $user = User::factory()->create();
    $user->createCustomerWithDriver();
    $customer = $user->fresh()->asCustomer();

    expect($customer)
        ->toBeInstanceOf(Customer::class)
        ->toBeObject();
});

test('a customer can add a payment method', function () {
    $user = User::factory()->create();
    $user->createCustomerWithDriver();

    $paymentMethod = $user->addPaymentMethod('pm_card_visa');

    expect($paymentMethod)
        ->toBeObject();
});

test('a customer can retrive their default payment method', function () {
    $user = User::factory()->create();
    $user->createCustomerWithDriver();
    $user->addPaymentMethod('pm_card_visa');

    $paymentMethod = $user->getDefaultPayment();
    $user = $user->fresh();

    expect($paymentMethod)
        ->not->toBeNull()
        ->toBeObject()
        ->toBeInstanceOf(PaymentMethod::class)
        ->and($paymentMethod->id)->toEqual($user->service_payment_id);
});

test('a charge save the transaction and items', function () {
    $user = User::factory()->create();
    $user->createCustomerWithDriver();
    $user->addPaymentMethod('pm_card_visa');

    expect(Transaction::all())
        ->toHaveCount(0)
        ->and(TransactionItem::all())->toHaveCount(0);

    $user->charge([[
        'name' => 'Product 01',
        'price' => 100,
    ], [
        'name' => 'Product 02',
        'price' => 200,
    ]]);

    expect(Transaction::all())
        ->toHaveCount(1)
        ->and(TransactionItem::all())->toHaveCount(2);
});

test('a charge save the address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();
    $user->createCustomerWithDriver();
    $user->addPaymentMethod('pm_card_visa');

    $payment = $user
        ->shipping($address)
        ->charge(['name' => 'Product 01', 'price' => 500]);

    expect($payment->asDriver())->toHaveKey('shipping')
        ->and($payment->asDriver()->shipping)->not->toBeNull();
});

test('a charge can have discount', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();
    $user->createCustomerWithDriver();
    $user->addPaymentMethod('pm_card_visa');

    $payment = $user
        ->discount(200)
        ->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

    expect($payment->getTransaction()->total)->not->toBeNull()
        ->and($payment->getTransaction()->discount)->not->toBeNull()
        ->and($payment->getTransaction()->amount)->toEqual(300)
        ->and($payment->getTransaction()->total)->toEqual(500)
        ->and($payment->getTransaction()->discount)->toEqual(200);
});

test('a user can create a charge with one time token payment method', function () {
    $user = User::factory()->create();
    $user->createCustomerWithDriver();

    expect(Transaction::all())
        ->toHaveCount(0);

    $user
        ->token('tok_visa')
        ->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

    expect(Transaction::all())
        ->toHaveCount(1);
});

test('an anonymous user can create a charge with one time token payment method', function () {
    $user = User::factory()->create();

    expect(Transaction::all())
        ->toHaveCount(0);

    $user
        ->token('tok_visa')
        ->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

    expect(Transaction::all())
        ->toHaveCount(1);
});

test('a user can create a charge with oxxo payment method', function () {
    $user = User::factory()->create();
    $user->createCustomerWithDriver();
    $user->phone = '+520000000000';

    expect(Transaction::all())
        ->toHaveCount(0);

    $user
        ->oxxo()
        ->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

    expect(Transaction::all())
        ->toHaveCount(1);
});

test('an anonymous user can create a charge with oxxo payment method', function () {
    $user = User::factory()->create();
    $user->phone = '+520000000000';

    expect(Transaction::all())
        ->toHaveCount(0);

    $user
        ->oxxo()
        ->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

    expect(Transaction::all())
        ->toHaveCount(1);
});
