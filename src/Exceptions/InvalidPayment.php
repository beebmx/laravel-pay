<?php

namespace Beebmx\LaravelPay\Exceptions;

use Exception;

class InvalidPayment extends Exception
{
    public static function purchase(): static
    {
        return new static('The payment requires at least 1 purchase.');
    }

    public static function price(array $purchase): static
    {
        return new static("The purchase {$purchase['name']} requires prices.");
    }
}
