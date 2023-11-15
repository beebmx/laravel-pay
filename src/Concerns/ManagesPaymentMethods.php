<?php

namespace Beebmx\LaravelPay\Concerns;

use Beebmx\LaravelPay\Elements\PaymentMethod;

trait ManagesPaymentMethods
{
    protected ?PaymentMethod $oneTimeTokenPaymentMethod = null;

    protected ?PaymentMethod $oxxoPaymentMethod = null;

    public function hasDefaultPayment(): bool
    {
        return ! is_null($this->service_payment_id);
    }

    public function getDefaultPayment(): ?PaymentMethod
    {
        if (! $this->hasDefaultPayment()) {
            return null;
        }

        return $this->findPaymentMethod($this->service_payment_id);
    }

    public function findPaymentMethod(string $paymentMethod): PaymentMethod
    {
        return new PaymentMethod($this, $this->driver()->findPaymentMethod($paymentMethod, $this->service_customer_id));
    }

    public function addPaymentMethod($paymentMethod)
    {
        $this->hasValidCustomerDriver();

        $driverPaymentMethod = $this
            ->driver()
            ->addPaymentMethod($paymentMethod, $this->service_customer_id);

        if (! $this->hasDefaultPayment()) {
            $this->fillPaymentMethodFields($driverPaymentMethod);
        }

        return $driverPaymentMethod;
    }

    public function token(string $token): static
    {
        $customer = ! $this->isCustomer()
            ? $this->asAnonymousCustomer()
            : $this->asCustomer();

        $this->oneTimeTokenPaymentMethod = new PaymentMethod($this, $this->driver()->token($token, $customer));

        return $this;
    }

    public function oxxo(): static
    {
        $this->oxxoPaymentMethod = new PaymentMethod($this, $this->driver()->oxxo($this));

        return $this;
    }

    public function hasOneTimePaymentMethod(): bool
    {
        return ! is_null($this->oneTimeTokenPaymentMethod) || ! is_null($this->oxxoPaymentMethod);
    }

    public function hasOneTimeTokenPaymentMethod(): bool
    {
        return ! is_null($this->oneTimeTokenPaymentMethod);
    }

    public function hasOxxoPaymentMethod(): bool
    {
        return ! is_null($this->oxxoPaymentMethod);
    }

    public function getOneTimeTokenPaymentMethod(): ?PaymentMethod
    {
        return $this->oneTimeTokenPaymentMethod;
    }

    public function getOxxoPaymentMethod(): ?PaymentMethod
    {
        return $this->oxxoPaymentMethod;
    }

    protected function fillPaymentMethodFields($paymentMethod)
    {
        $this->forceFill([
            'service_payment_id' => $paymentMethod->id,
            'service_payment_type' => $this->driver()::getPaymentType($paymentMethod),
            'service_payment_brand' => $this->driver()::getPaymentBrand($paymentMethod),
            'service_payment_last_four' => $this->driver()::getPaymentLastFour($paymentMethod),
        ])->save();
    }
}
