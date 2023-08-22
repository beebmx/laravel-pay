<?php

namespace Beebmx\LaravelPay\Drivers;

use Beebmx\LaravelPay\Discount;
use Beebmx\LaravelPay\Elements\Customer as PayCustomer;
use Beebmx\LaravelPay\Elements\PaymentMethod;
use Conekta\Conekta;
use Conekta\Customer;
use Conekta\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ConektaDriver extends Driver
{
    const DRIVER_NAME = 'conekta';

    const DRIVER_VERSION = '2.0.0';

    protected int $unit = 100;

    public static function urls(): array
    {
        return [
            'api' => 'https://api.conekta.io',
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
                'type' => $type,
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

    public function oxxo($user): object
    {
        return (object) [
            'type' => 'oxxo_cash',
            'expires_at' => Carbon::now()
                ->addDays((int) config('pay.oxxo.days_to_expire'))
                                ->timestamp,
        ];
    }

    public function charge(PayCustomer $customer, PaymentMethod $paymentMethod, array $products, Discount $discount, $address = null, array $options = []): object
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
            $this->prepareDiscount($discount),
        )), function ($order) use ($paymentMethod) {
            $order->status = $order->payment_status;

            if ($paymentMethod->type === 'oxxo_cash') {
                $order->oxxo_reference = $order->charges[0]['payment_method']['reference'];
            }
        });
    }

    public function webhook(string $url): void
    {
        Http::withHeaders([
            'Accept' => 'application/vnd.conekta-v2.0.0+json',
            'Authorization' => $this->getAuthorization(),
            'Content-Type' => 'application/json',
        ])->post("{$this->getApiUrl()}/webhooks", [
            'url' => $url,
            'synchronous' => false,
        ]);
    }

    public function webhookList(string $url): array
    {
        return $this->getWebhooksList($url)
            ->map(fn ($webhook) => [
                'id' => $webhook['id'],
                'status' => $webhook['status'],
                'url' => $webhook['url'],
                'production' => $webhook['production_enabled'] ? '✅' : '❎',
            ])->toArray();
    }

    public function webhookDestroy(string $url): void
    {
        $this->getWebhooksList($url)
            ->filter(fn ($webhook) => $webhook['url'] === $url)
            ->each(function ($webhook) use ($url) {
                Http::withHeaders([
                    'Accept' => 'application/vnd.conekta-v2.0.0+json',
                    'Authorization' => $this->getAuthorization(),
                    'Content-Type' => 'application/json',
                ])->delete("{$this->getApiUrl()}/webhooks/{$webhook['id']}", [
                    'url' => $url,
                    'synchronous' => false,
                ]);
            });
    }

    protected function prepareCustomer($customer): array
    {
        if ($this->isNotConektaCustomer($customer->asDriver())) {
            return [
                'customer_info' => Collection::make([
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ])->filter(function ($value) {
                    return ! empty($value);
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
                'payment_source_id' => $paymentMethod->id,
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
                    return ! empty($value);
                })->toArray(),
            ],
        ];
    }

    protected function prepareDiscount(Discount $discount): array
    {
        if ($discount->amount === 0) {
            return [];
        }

        return [
            'discount_lines' => [[
                'type' => 'coupon',
                'amount' => $this->preparePrice($discount->amount),
                'code' => $discount->code ?? Str::random(),
            ]],
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
        return $paymentMethod->token_id !== null || $paymentMethod->type === 'oxxo_cash';
    }

    protected function getApiUrl(): string
    {
        return static::urls()['api'];
    }

    protected function getAuthorization(): string
    {
        return 'Basic '.base64_encode(config('pay.drivers.conekta.secret'));
    }

    protected function getWebhooksList($url): Collection
    {
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.conekta-v2.0.0+json',
            'Authorization' => $this->getAuthorization(),
            'Content-Type' => 'application/json',
        ])->get("{$this->getApiUrl()}/webhooks", [
            'url' => $url,
            'synchronous' => false,
        ]);

        return Collection::make($response->json()['data']);
    }
}
