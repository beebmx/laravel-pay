<?php

namespace Beebmx\LaravelPay;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem
{
    public function __construct(public string $name, public float $price, public $quantity = 1, public Model|int|null $model = null)
    {
    }
}
