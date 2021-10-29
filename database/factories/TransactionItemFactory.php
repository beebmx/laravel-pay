<?php

namespace Beebmx\LaravelPay\Database\Factories;

use Beebmx\LaravelPay\Tests\Fixtures\User;
use Beebmx\LaravelPay\Transaction;
use Beebmx\LaravelPay\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TransactionItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'transaction_id',
            'name',
            'price',
            'quantity',
            'product_id',
        ];
    }
}
