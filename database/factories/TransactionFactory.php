<?php

namespace Beebmx\LaravelPay\Database\Factories;

use Beebmx\LaravelPay\Pay;
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
        $user = Pay::$customerModel;

        return [
            (new $user)->getForeignKey() => ($user)::factory(),
            'service' => config('pay.default'),
            'service_id' => 'src_' . Str::random(17),
            'service_type' => 'card',
            'service_payment_id' => 'ord_' . Str::random(17),
            'amount' => $this->faker->numberBetween(100, 1000),
            'discount' => 0,
            'currency' => 'usd',
            'status' => $this->faker->randomElement(['pending', 'paid', 'canceled']),
        ];
    }
}
