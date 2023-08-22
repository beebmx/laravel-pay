<?php

namespace Beebmx\LaravelPay\Exceptions;

use Exception;

class InvalidDriver extends Exception
{
    public static function exists(string $driver): static
    {
        return new static("The driver {$driver} is invalid.");
    }

    public static function customerRequireDriver($customer): static
    {
        return new static(class_basename($customer).' requires to set a driver before requested.');
    }
}
