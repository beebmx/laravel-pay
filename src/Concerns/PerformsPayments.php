<?php

namespace Beebmx\LaravelPay\Concerns;

use Beebmx\LaravelPay\Elements\Payment;
use Beebmx\LaravelPay\Elements\PaymentMethod;
use Beebmx\LaravelPay\Exceptions\InvalidPayment;
use Beebmx\LaravelPay\Exceptions\InvalidPaymentMethod;
use Beebmx\LaravelPay\PurchaseItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait PerformsPayments
{
    /**
     * @throws InvalidPayment
     * @throws InvalidPaymentMethod
     * @throws \Beebmx\LaravelPay\Exceptions\DriverNonInstantiable
     * @throws \Beebmx\LaravelPay\Exceptions\InvalidCustomer
     * @throws \Beebmx\LaravelPay\Exceptions\InvalidDriver
     */
    public function charge(array $purchase, PaymentMethod $paymentMethod = null, array $options = []): Payment
    {
        if (empty($purchase)) {
            throw InvalidPayment::purchase();
        }

        $paymentMethod = match (true) {
            $this->hasOneTimeTokenPaymentMethod() => $this->getOneTimeTokenPaymentMethod(),
            $this->hasOxxoPaymentMethod() => $this->getOxxoPaymentMethod(),
            default => $paymentMethod
        };

        if (! $paymentMethod && ! $this->hasDefaultPayment()) {
            throw InvalidPaymentMethod::exists();
        }

        $purchases = $this->isPurchase($purchase)
            ? [$this->parsePurchase($purchase)]
            : Collection::make($purchase)->map(function ($item) {
                return $this->parsePurchase($item);
            })->values()->all();

        $paymentMethod = $this->getPaymentMethod($paymentMethod);

        $customer = $this->hasOneTimePaymentMethod() && ! $this->isCustomer()
            ? $this->asAnonymousCustomer()
            : $this->asCustomer();

        $payment = $this->driver()->charge(
            $customer,
            $paymentMethod,
            $purchases,
            $this->getDiscount(),
            $this->currentShippingAddress(),
            $options
        );

        $transaction = $this->transactions()->create([
            'service' => $this->driver()->name(),
            'service_id' => $this->getPaymentMethodId($paymentMethod, $payment),
            'service_type' => $paymentMethod->type ?? $payment->type,
            'service_payment_id' => $payment->id,
            'amount' => $this->driver()->parsePrice($payment->amount),
            'total' => $this->driver()->parsePrice($payment->amount) + $this->getDiscount()->amount,
            'discount' => $this->getDiscount()->amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'payload' => $payment,
            'address_id' => $this->currentShippingAddress()?->id,
            'shipping' => $this->currentShippingAddress()?->asString(),
        ]);

        $transaction->addItems($purchases);

        if ($this->hasShipping()) {
            $this->shipping(null);
        }

        return tap(new Payment($this, $payment), function ($payment) use ($transaction) {
            $payment->setTransaction($transaction);
        });
    }

    protected function isPurchase(array $purchase): bool
    {
        return Collection::make($purchase)->first(function ($item) {
            return is_array($item);
        }) === null;
    }

    protected function parsePurchase(array $purchase): PurchaseItem
    {
        if (! $this->isPurchaseValid($purchase)) {
            throw InvalidPayment::price($purchase);
        }

        return new PurchaseItem(
            name: $purchase['name'],
            price: $purchase['price'],
            quantity: $purchase['quantity'] ?? 1,
            model: $this->prepareModel($purchase['model'] ?? null)
        );
    }

    protected function isPurchaseValid(array $purchase): bool
    {
        return array_key_exists('price', $purchase);
    }

    protected function getPaymentMethod(PaymentMethod|string|null $paymentMethod): PaymentMethod
    {
        if ($paymentMethod) {
            return $paymentMethod;
        }

        return $this->getDefaultPayment();
    }

    public function getPaymentMethodId(PaymentMethod $paymentMethod, $payment = null)
    {
        return match (true) {
            ! is_null($paymentMethod->id) => $paymentMethod->id,
            ! is_null($paymentMethod->token) => $paymentMethod->token,
            isset($payment->oxxo_reference) => $payment->oxxo_reference,
            isset($payment->oxxo_secret) => $payment->oxxo_secret,
        };
    }

    protected function prepareModel(Model|int|null $model): ?int
    {
        if ($model instanceof Model) {
            return $model->getKey();
        }

        if (is_int($model)) {
            return $model;
        }

        return null;
    }
}
