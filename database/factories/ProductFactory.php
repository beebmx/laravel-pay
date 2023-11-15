<?php

namespace Beebmx\LaravelPay\Database\Factories;

use Beebmx\LaravelPay\Tests\Fixtures\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4),
            'price' => fake()->numberBetween(500, 1000),
            'stock' => fake()->numberBetween(10, 100),
            'sku' => Str::random(12),
            'barcode' => fake()->ean13(),

        ];
    }
}
