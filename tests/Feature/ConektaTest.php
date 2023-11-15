<?php

use Beebmx\LaravelPay\Address;
use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Elements\PaymentMethod;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Beebmx\LaravelPay\Transaction;
use Beebmx\LaravelPay\TransactionItem;

beforeEach(function () {
    config(['pay.default' => 'conekta']);
    config(['pay.currency' => 'mxn']);
    config(['pay.oxxo.days_to_expire' => 1]);
})->skip(
    (getenv('PAY_TEST_FULL_SUITE') === '(false)' || ! getenv('PAY_TEST_FULL_SUITE')) || ! getenv('CONEKTA_SECRET_KEY'),
    'Conekta Test are skipped'
);

test('a customer can be created', function () {
    $user = User::factory()->create();

    $customer = $user->createCustomerWithDriver();

    expect($user->service)
        ->not->toBeNull()
        ->toEqual('conekta')
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

    $paymentMethod = $user->addPaymentMethod('tok_test_visa_4242');

    expect($paymentMethod)
        ->toBeObject();
});

test('a customer can retrive their default payment method', function () {
    $user = User::factory()->create();
    $user->createCustomerWithDriver();
    $user->addPaymentMethod('tok_test_visa_4242');

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
    $user->addPaymentMethod('tok_test_visa_4242');

    expect(Transaction::all())->toHaveCount(0)
        ->and(TransactionItem::all())->toHaveCount(0);

    $user->charge([[
        'name' => 'Product 01',
        'price' => 100,
    ], [
        'name' => 'Product 02',
        'price' => 200,
    ]]);

    expect(Transaction::all())->toHaveCount(1)
        ->and(TransactionItem::all())->toHaveCount(2);
});

test('a charge save the address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user)->create();
    $user->createCustomerWithDriver();
    $user->addPaymentMethod('tok_test_visa_4242');

    $payment = $user
        ->shipping($address)
        ->charge(['name' => 'Product 01', 'price' => 500]);

    expect($payment->asDriver())
        ->toHaveKey('shipping_contact');
});

test('a charge can have discount', function () {
    $user = User::factory()->create();
    $user->createCustomerWithDriver();
    $user->addPaymentMethod('tok_test_visa_4242');

    $payment = $user
        ->discount(['amount' => 200, 'code' => '200coupon'])
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
        ->token('tok_test_visa_4242')
        ->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

    expect(Transaction::all())
        ->toHaveCount(1);
});

test('an anonymous user can create a charge with one time token payment method', function () {
    $user = User::factory()->create();
    $user->phone = '+520000000000';

    expect(Transaction::all())
        ->toHaveCount(0);

    $user
        ->token('tok_test_visa_4242')
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
