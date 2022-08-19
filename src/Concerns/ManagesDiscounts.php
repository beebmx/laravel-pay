<?php

namespace Beebmx\LaravelPay\Concerns;

use Beebmx\LaravelPay\Discount;

trait ManagesDiscounts
{
    protected int|float|array $paymentDiscount = 0;

    public function discount(int|float|array $amount): static
    {
        $this->paymentDiscount = $amount;

        return $this;
    }

    public function getDiscount()
    {
        if (is_float($this->paymentDiscount) || is_int($this->paymentDiscount)) {
            return new Discount((float) $this->paymentDiscount);
        }

        return new Discount($this->paymentDiscount['amount'] ?? 0, $this->paymentDiscount['code'] ?? null, $this->paymentDiscount);
    }
}
