<?php

namespace Beebmx\LaravelPay\Exceptions;

use Exception;

class CustomerAlreadyExists extends Exception
{
    public static function exists($customer): static
    {
        return new static(class_basename($customer)." is already a customer with ID {$customer->driver_customer_id}.");
    }
}
