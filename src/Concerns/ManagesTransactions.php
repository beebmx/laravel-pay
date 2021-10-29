<?php

namespace Beebmx\LaravelPay\Concerns;

use Beebmx\LaravelPay\Pay;

trait ManagesTransactions
{
    public function transactions()
    {
        return $this->hasMany(Pay::$transactionModel, $this->getForeignKey())->orderByDesc('created_at');
    }
}
