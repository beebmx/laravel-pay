<?php

namespace Beebmx\LaravelPay\Elements;

use Beebmx\LaravelPay\Transaction;

class Payment extends Element
{
    protected Transaction $transaction;

    public function setTransaction(Transaction $transaction): static
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
