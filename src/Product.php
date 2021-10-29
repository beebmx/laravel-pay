<?php

namespace Beebmx\LaravelPay;

class Product
{
    public function __construct(public string $name, public float $price, public $quantity = 1)
    {
    }
}
