<?php

namespace Beebmx\LaravelPay\Concerns;

use Beebmx\LaravelPay\Transaction;

trait HasWebhookHandler
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    protected function find(string $payment_id)
    {
        return Transaction::findByPaymentId($payment_id);
    }
}
