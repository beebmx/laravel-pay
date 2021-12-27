<?php

namespace Beebmx\LaravelPay\Concerns;

use Beebmx\LaravelPay\Elements\Payment;
use Beebmx\LaravelPay\Elements\PaymentMethod;
use Beebmx\LaravelPay\Exceptions\InvalidPayment;
use Beebmx\LaravelPay\Exceptions\InvalidPaymentMethod;
use Beebmx\LaravelPay\Product;
use Illuminate\Support\Collection;

trait PerformsPayments
{
    /**
     * @param array $product
     * @param PaymentMethod|null $paymentMethod
     * @throws InvalidPayment
     * @throws InvalidPaymentMethod
     * @throws \Beebmx\LaravelPay\Exceptions\DriverNonInstantiable
     * @throws \Beebmx\LaravelPay\Exceptions\InvalidCustomer
     * @throws \Beebmx\LaravelPay\Exceptions\InvalidDriver
     */
    public function charge(array $product, PaymentMethod $paymentMethod = null, array $options = []): Payment
    {
        if (empty($product)) {
            throw InvalidPayment::product();
        }

        $paymentMethod = match(true) {
            $this->hasOneTimeTokenPaymentMethod() => $this->getOneTimeTokenPaymentMethod(),
            $this->hasOxxoPaymentMethod() => $this->getOxxoPaymentMethod(),
            default => $paymentMethod
        };

        if (!$paymentMethod && !$this->hasDefaultPayment()) {
            throw InvalidPaymentMethod::exists();
        }

        $products = $this->isProduct($product)
            ? [$this->parseProduct($product)]
            : Collection::make($product)->map(function ($item) {
                return $this->parseProduct($item);
            })->values()->all();

        $paymentMethod = $this->getPaymentMethod($paymentMethod);

        $customer = $this->hasOneTimePaymentMethod() && !$this->isCustomer()
            ? $this->asAnonymousCustomer()
            : $this->asCustomer();

        $payment = $this->driver()->charge(
            $customer,
            $paymentMethod,
            $products,
            $this->currentShippingAddress(),
            $options
        );


        $transaction = $this->transactions()->create([
            'service' => $this->driver()->name(),
            'service_id' => $this->getPaymentMethodId($paymentMethod, $payment),
            'service_type' => $paymentMethod->type ?? $payment->type,
            'service_payment_id' => $payment->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'payload' => $payment,
            'address_id' => $this->currentShippingAddress()?->id,
            'shipping' => $this->currentShippingAddress()?->asString(),
        ]);

        $transaction->addItems($products);

        if ($this->hasShipping()) {
            $this->shipping(null);
        }

        return tap(new Payment($this, $payment), function ($payment) use ($transaction) {
            $payment->setTransaction($transaction);
        });
    }

    protected function isProduct(array $product): bool
    {
        return Collection::make($product)->first(function ($item) {
            return is_array($item);
        }) === null;
    }

    protected function parseProduct(array $product): Product
    {
        if (!$this->isProductValid($product)) {
            throw InvalidPayment::price($product);
        }

        return new Product($product['name'], $product['price'], $product['quantity'] ?? 1);
    }

    protected function isProductValid(array $product): bool
    {
        return array_key_exists('price', $product);
    }

    /**
     * @param string|PaymentMethod|null $paymentMethod
     * @return PaymentMethod
     */
    protected function getPaymentMethod(PaymentMethod|string|null $paymentMethod): PaymentMethod
    {
        if ($paymentMethod) {
            return $paymentMethod;
        }

        return $this->getDefaultPayment();
    }

    public function getPaymentMethodId(PaymentMethod $paymentMethod, $payment = null)
    {
        return match(true) {
            !is_null($paymentMethod->id) => $paymentMethod->id,
            !is_null($paymentMethod->token) => $paymentMethod->token,
            !is_null($payment->oxxo_reference) => $payment->oxxo_reference,
            !is_null($payment->oxxo_secret) => $payment->oxxo_secret
        };
    }
}
