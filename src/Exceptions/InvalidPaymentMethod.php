<?php

namespace Beebmx\LaravelPay\Exceptions;

use Exception;

class InvalidPaymentMethod extends Exception
{
    public static function exists(): static
    {
        return new static('The payment requires a valid payment method.');
    }
}
