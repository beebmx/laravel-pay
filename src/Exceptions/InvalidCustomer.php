<?php

namespace Beebmx\LaravelPay\Exceptions;

use Exception;

class InvalidCustomer extends Exception
{
    public static function exists($customer): static
    {
        return new static(class_basename($customer).' is not a customer yet. Create a customer before continue.');
    }
}
