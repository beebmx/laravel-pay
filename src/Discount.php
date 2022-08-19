<?php

namespace Beebmx\LaravelPay;

class Discount
{
    public function __construct(public int|float $amount, public ?string $code = null, array  $payload = [])
    {
    }
}
