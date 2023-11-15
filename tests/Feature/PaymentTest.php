<?php

use Beebmx\LaravelPay\Elements\Payment;
use Beebmx\LaravelPay\Exceptions\InvalidPayment;
use Beebmx\LaravelPay\Exceptions\InvalidPaymentMethod;
use Beebmx\LaravelPay\Tests\Fixtures\Product;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Beebmx\LaravelPay\Transaction;
use Beebmx\LaravelPay\TransactionItem;
use Illuminate\Support\Facades\DB;

use function Orchestra\Testbench\workbench_path;

beforeEach(function () {
    $this->loadMigrationsFrom(workbench_path('/database/migrations'));
});

test('a charge requires at least one product', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

    $user->charge([]);
})->throws(InvalidPayment::class);

it('requires a payment method defined to perform a payment', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789']);

    $user->charge([['name' => 'Product 01', 'price' => 500]]);
})->throws(InvalidPaymentMethod::class);

test('a charge requires at least price in product', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

    $user->charge(['name' => 'Product name']);
})->throws(InvalidPayment::class);

test('a charge with many products requires always prices key', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

    $user->charge([['name' => 'Product 01', 'price' => 500], ['name' => 'Product 02']]);
})->throws(InvalidPayment::class);

test('a charge performs a payment', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

    $payment = $user->charge([
        'name' => 'Testing product',
        'price' => 500,
    ]);

    expect($payment)
        ->toBeInstanceOf(Payment::class);
});

test('a charge save the transaction', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

    expect(Transaction::all())
        ->toHaveCount(0);

    $user->charge([
        'name' => 'Testing product',
        'price' => 500,
    ]);

    expect(Transaction::all())
        ->toHaveCount(1);
});

test('a charge save the transaction items', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

    expect(TransactionItem::all())
        ->toHaveCount(0);

    $user->charge([[
        'name' => 'Product 01',
        'price' => 100,
    ], [
        'name' => 'Product 02',
        'price' => 200,
    ]]);

    expect(TransactionItem::all())
        ->toHaveCount(2);
});

test('a charge saved can return the transaction', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

    $payment = $user->charge([
        'name' => 'Product 01',
        'price' => 100,
    ]);

    expect($payment->getTransaction())->toBeInstanceOf(Transaction::class)
        ->and($payment->getTransaction()->id)->not->toBeNull()
        ->and($payment->getTransaction()->service_id)->toEqual('src_0123456789');
});

test('a charge payment can have shipping', function () {
    $user = User::factory()->hasAddresses()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);
    $address = $user->addresses()->first();

    $payment = $user
        ->shipping($address)
        ->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

    expect($payment->getTransaction()->address_id)->not->toBeNull()
        ->and($payment->getTransaction()->shipping)->not->toBeNull()
        ->and($payment->getTransaction()->address_id)->toEqual($address->id);

    $payment = $user->charge([
        'name' => 'Testing product',
        'price' => 500,
    ]);

    expect($payment->getTransaction()->address_id)->toBeNull()
        ->and($payment->getTransaction()->shipping)->toBeNull();
});

test('a charge payment can have discount', function () {
    $user = User::factory()->create(['service_customer_id' => 'cus_0123456789', 'service_payment_id' => 'src_0123456789']);

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

test('a user can create a charge with one time payment method', function () {
    $user = User::factory()->create();

    expect(Transaction::all())
        ->toHaveCount(0);

    $user
        ->token('tok_visa4242')
        ->charge([
            'name' => 'Testing product',
            'price' => 500,
        ]);

    expect(Transaction::all())
        ->toHaveCount(1);
});

test('a user can create a charge with oxxo payment', function () {
    $user = User::factory()->create();

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

it('can migrate a products table', function () {
    $migrations = DB::table('migrations')->get();

    expect($migrations->contains('migration', '2021_09_01_000004_create_products_table'))->toBeTrue();
});

it('can add a model to transaction', function () {
    $user = User::factory()->create();
    $product01 = Product::factory()->create();
    $product02 = Product::factory()->create();

    $user->token('tok_visa4242')->charge([[
        'name' => 'Product 01',
        'price' => 100,
        'model' => $product01,
    ], [
        'name' => 'Product 02',
        'price' => 200,
        'model' => $product02,
    ]]);

    expect(TransactionItem::all())->toHaveCount(2)
        ->and(TransactionItem::first()->model_id)->toEqual($product01->id)
        ->and(TransactionItem::orderByDesc('id')->first()->model_id)->toEqual($product02->id);
});

it('can add a model with int to transaction', function () {
    $user = User::factory()->create();
    Product::factory()->count(2)->create();

    $user->token('tok_visa4242')->charge([[
        'name' => 'Product 01',
        'price' => 100,
        'model' => 1,
    ], [
        'name' => 'Product 02',
        'price' => 200,
        'model' => 2,
    ]]);

    expect(TransactionItem::all())->toHaveCount(2)
        ->and(TransactionItem::first()->model_id)->toEqual(1)
        ->and(TransactionItem::orderByDesc('id')->first()->model_id)->toEqual(2);
});
