<?php

namespace Beebmx\LaravelPay\Concerns;

use Beebmx\LaravelPay\Pay;

trait ManagesAddresses
{
    protected $shippingAddress;

    public function addresses()
    {
        return $this->hasMany(Pay::$addressModel, $this->getForeignKey());
    }

    public function currentShippingAddress()
    {
        return $this->shippingAddress;
    }

    public function hasShipping(): bool
    {
        return ! is_null($this->shippingAddress);
    }

    public function shipping($address): static
    {
        $this->shippingAddress = $address;

        return $this;
    }
}
