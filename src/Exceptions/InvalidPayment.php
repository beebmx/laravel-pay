<?php

namespace Beebmx\LaravelPay\Exceptions;

use Exception;

class InvalidPayment extends Exception
{
    public static function product(): static
    {
        return new static('The payment requires at least 1 product.');
    }

    public static function price(array $product): static
    {
        return new static("The product {$product['name']} requires prices.");
    }
}
