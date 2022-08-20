<?php

namespace Beebmx\LaravelPay\Elements;

class Payment extends Element
{
    protected $transaction;

    public function setTransaction($transaction): static
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }
}
