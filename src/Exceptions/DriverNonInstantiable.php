<?php

namespace Beebmx\LaravelPay\Exceptions;

use Exception;

class DriverNonInstantiable extends Exception
{
    public static function invalid($driver): static
    {
        return new static("The driver {$driver} can not be instantiable.");
    }
}
