<?php

namespace Beebmx\LaravelPay\Drivers;

use Beebmx\LaravelPay\Elements\Customer as PayCustomer;
use Beebmx\LaravelPay\Elements\PaymentMethod;
use Beebmx\LaravelPay\Pay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeDriver extends Driver
{
    const DRIVER_NAME = 'stripe';

    const DRIVER_VERSION = '2020-08-27';

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
        Stripe::setAppInfo(
            'Laravel Pay',
            Pay::VERSION,
            'https://github.com/beebmx/laravel-pay'
        );
    }

    public function customerCreate(array $options = []): object
    {
        return $this->stripe()->customers->create($options);
    }

    public function asCustomer(string $customerId, array $options = []): object
    {
        return $this->stripe()->customers->retrieve(
            $customerId,
            ['expand' => $options]
        );
    }

    public function addPaymentMethod($paymentMethod, $customerId = null, $type = 'card'): object
    {
        return $this->retrivePaymentMethod($paymentMethod)
                    ->attach(['customer' => $customerId]);
    }

    public static function getPaymentType($paymentMethod)
    {
        return $paymentMethod->type;
    }

    public static function getPaymentBrand($paymentMethod)
    {
        return $paymentMethod->card->brand;
    }

    public static function getPaymentLastFour($paymentMethod)
    {
        return $paymentMethod->card->last4;
    }

    public function findPaymentMethod(string $paymentMethod, $customerId = null, $type = 'card'): object
    {
        $methods = $this->stripe()
            ->paymentMethods
            ->all(['customer' => $customerId, 'type' => $type]);

        return Collection::make($methods->data)->first(function ($method) use ($paymentMethod) {
            return $method->id === $paymentMethod;
        });
    }

    public function token(string $token): object
    {
        return $this->stripe()->paymentMethods->create([
            'type' => 'card',
            'card' => [
                'token' => $token
            ],
        ]);
    }

    public function oxxo(): object
    {
        return (object) [
            'payment_method_types' => ['oxxo'],
            'payment_method_options' => [
                'oxxo' =>[
                    'expires_after_days' => (int) config('pay.oxxo.days_to_expire')
                ]
            ]
        ];
    }

    public function charge(PayCustomer $customer, PaymentMethod $paymentMethod, array $products, $address = null, $options = []): object
    {
        return tap($this->stripe()->paymentIntents->create(array_merge(
            [
                'amount' => $this->preparePrice(
                    $this->getProductsAmount($products)
                ),
                'currency' => config('pay.currency'),
            ],
            $this->preparePaymentMethod($paymentMethod),
            $this->prepareCustomer($customer),
            $this->prepareShipping($address, $customer),
        )), function ($order) {
            if ($this->isAnOxxoOrder($order)) {
                $order->oxxo_secret = $order->client_secret;
                $order->type = 'oxxo';
            }
        });
    }

    public function webhook(string $url): void
    {
        $this->stripe()->webhookEndpoints->create([
            'url' => $url,
            'enabled_events' => config('pay.drivers.stripe.webhooks.events'),
        ]);
    }

    public function webhookList(string $url): array
    {
        return $this->getWebhooksList()
            ->map(fn($webhook) => [
                'id' => $webhook['id'],
                'status' => $webhook['status'],
                'url' => $webhook['url'],
                'production' => $webhook['livemode'] ? '✅' : '❎'
            ])->toArray();
    }

    public function webhookDestroy(string $url): void
    {
        $this->getWebhooksList($url)
            ->filter(fn($webhook) => $webhook['url'] === $url)
            ->each(fn($webhook) =>
                $this->stripe()->webhookEndpoints->delete($webhook['id'], [])
            );
    }

    protected function getProductsAmount(array $products)
    {
        return Collection::make($products)->sum(
            fn ($product) => $product->price * $product->quantity
        );
    }

    protected function preparePaymentMethod(PaymentMethod $paymentMethod): array
    {
        if ($paymentMethod->id) {
            return [
                'payment_method' => $paymentMethod->id,
                'confirm' => true,
                'confirmation_method' => 'automatic',
            ];
        }

        return $paymentMethod->toArray();
    }

    protected function prepareCustomer($customer): array
    {
        if ($this->isNotStripeCustomer($customer->asDriver())) {
            return [
                'description' => "{$customer->email} ({$customer->name})",
            ];
        }

        return [
            'customer' => $customer->id,
        ];
    }

    protected function prepareShipping($address, $customer): array
    {
        if (is_null($address)) {
            return [];
        }

        return [
            'shipping' => [
                'address' => Collection::make([
                    'line1' => $address->line1,
                    'line2' => $address->line2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                ])->filter(function ($value) {
                    return !empty($value);
                })->toArray(),
                'name' => $customer->name,
            ],
        ];
    }

    protected function isNotStripeCustomer($customer): bool
    {
        return is_null($customer->id);
    }

    protected function isPaymentMethodMerge($paymentMethod): bool
    {
        return $paymentMethod->token_id !== null;
    }

    protected function retrivePaymentMethod($paymentMethod)
    {
        if (is_string($paymentMethod)) {
            return $this->stripe()->paymentMethods->retrieve($paymentMethod);
        }

        return $paymentMethod;
    }

    protected function isAnOxxoOrder($order): bool
    {
        return ! is_null($order['payment_method_options']['oxxo']);
    }

    protected function getWebhooksList(): Collection
    {
        $response =  $this->stripe()->webhookEndpoints->all();

        return Collection::make($response->data);
    }

    protected function stripe(array $options = []): StripeClient
    {
        return new StripeClient(array_merge([
            'api_key' => config('pay.drivers.stripe.secret'),
            'stripe_version' => static::DRIVER_VERSION
        ], $options));
    }
}
