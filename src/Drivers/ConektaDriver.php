<?php

namespace Beebmx\LaravelPay\Drivers;

use Beebmx\LaravelPay\Elements\Customer as PayCustomer;
use Beebmx\LaravelPay\Elements\PaymentMethod;
use Conekta\Conekta;
use Conekta\Customer;
use Conekta\Order;
use Illuminate\Support\Collection;

class ConektaDriver extends Driver
{
    const DRIVER_NAME = 'conekta';

    const DRIVER_VERSION = '2.0.0';

    protected int $unit = 100;

    public static function urls(): array
    {
        return [
            'customers' => null,
            'logs' => null,
            'order' => null,
        ];
    }

    protected function boot()
    {
        Conekta::setApiKey(config('pay.drivers.conekta.secret'));
        Conekta::setApiVersion(static::DRIVER_VERSION);
        Conekta::setLocale(config('pay.locale'));
    }

    public function customerCreate(array $options = []): object
    {
        return Customer::create($options);
    }

    public function asCustomer(string $customerId, array $options = []): object
    {
        return Customer::find($customerId);
    }

    public function addPaymentMethod($paymentMethod, $customerId = null, $type = 'card'): object
    {
        return $this->asCustomer($customerId)
            ->createPaymentSource([
                'type'     => $type,
                'token_id' => $paymentMethod,
            ]);
    }

    public function findPaymentMethod(string $paymentMethod, $customerId = null, $type = 'card'): object
    {
        return Collection::make($this->asCustomer($customerId)->payment_sources)
            ->first(function ($paymentSource) use ($paymentMethod) {
                return $paymentSource->id === $paymentMethod;
            });
    }

    public function token(string $token): object
    {
        return (object) [
            'type' => 'card',
            'token_id' => $token,
            'token' => $token,
        ];
    }

    public function charge(PayCustomer $customer, PaymentMethod $paymentMethod, array $products, $address = null, $options = []): object
    {
        return tap(Order::create(array_merge(
            [
                'currency' => config('pay.currency'),
                'charges' => [
                    $this->prepareCharges($customer->asDriver(), $paymentMethod),
                ],
            ],
            $this->prepareCustomer($customer),
            $this->prepareItems($products),
            $this->prepareShipping($address),
        )), function ($order) {
            $order->status = $order->payment_status;
        });
    }

    protected function prepareCustomer($customer): array
    {
        if ($this->isNotConektaCustomer($customer->asDriver())) {
            return [
                'customer_info' => Collection::make([
                    "name" => $customer->name,
                    "email" => $customer->email,
                    "phone" => $customer->phone,
                ])->filter(function ($value) {
                    return !empty($value);
                })->toArray(),
            ];
        }

        return [
            'customer_info' => [
                'customer_id' => $customer->id,
            ],
        ];
    }

    protected function prepareItems(array $products): array
    {
        return [
            'line_items' => Collection::make($products)->map(fn ($product) => [
                'name' => $product->name,
                'unit_price' => $this->preparePrice($product->price),
                'quantity' => $product->quantity,
            ])->toArray(),
        ];
    }

    protected function prepareCharges($customer, $paymentMethod): array
    {
        if ($this->isPaymentMethodMerge($paymentMethod)) {
            return [
                'payment_method' => $paymentMethod->toArray(),
            ];
        }

        return [
            'payment_method' => [
                'type' => $paymentMethod->type,
                'payment_source_id' => $paymentMethod->id
            ],
        ];
    }

    protected function prepareShipping($address): array
    {
        if (is_null($address)) {
            return [];
        }

        return [
            'shipping_contact' => [
                'address' => Collection::make([
                    'street1' => $address->line1,
                    'street2' => $address->line2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                ])->filter(function ($value) {
                    return !empty($value);
                })->toArray(),
            ],
        ];
    }

    public static function getPaymentType($paymentMethod)
    {
        return $paymentMethod->type;
    }

    public static function getPaymentBrand($paymentMethod)
    {
        return $paymentMethod->brand;
    }

    public static function getPaymentLastFour($paymentMethod)
    {
        return $paymentMethod->last4;
    }

    protected function isNotConektaCustomer($customer): bool
    {
        return is_null($customer->id);
    }

    protected function isPaymentMethodMerge($paymentMethod): bool
    {
        return $paymentMethod->token_id !== null;
    }
}
