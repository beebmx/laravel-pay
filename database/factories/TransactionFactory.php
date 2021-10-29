<?php

namespace Beebmx\LaravelPay\Database\Factories;

use Beebmx\LaravelPay\Tests\Fixtures\User;
use Beebmx\LaravelPay\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id',
            'service',
            'service_id',
            'service_type',
            'service_payment_id',
            'amount',
            'currency',
            'status',
        ];
    }
}
