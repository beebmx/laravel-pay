<?php

namespace Beebmx\LaravelPay\Database\Factories;

use Beebmx\LaravelPay\Address;
use Beebmx\LaravelPay\Pay;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

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
            'city' => $this->faker->city(),
            'country' => $this->faker->countryCode(),
            'line1' => $this->faker->streetAddress(),
            'postal_code' => $this->faker->postcode,
            'state' => $this->faker->state(),
        ];
    }
}
