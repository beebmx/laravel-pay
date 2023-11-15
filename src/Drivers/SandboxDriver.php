<?php

namespace Beebmx\LaravelPay\Drivers;

use Beebmx\LaravelPay\Discount;
use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Elements\PaymentMethod;
use Beebmx\LaravelPay\Exceptions\WebhookUnavailable;
use Beebmx\LaravelPay\Pay;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SandboxDriver extends Driver
{
    const DRIVER_NAME = 'sandbox';

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
        //
    }

    public function customerCreate(array $options = []): object
    {
        return (object) $this->prepareCustomer(null, $options);
    }

    public function asCustomer(string $customerId, array $options = []): object
    {
        $customer = Pay::$customerModel::where('service_customer_id', $customerId)->first();

        return (object) $this->prepareCustomer($customerId, [
            'name' => $customer->name,
            'email' => $customer->email,
        ]);
    }

    public function addPaymentMethod($paymentMethod, $customerId = null, $type = 'card'): object
    {
        return (object) $this->preparePaymentMethod(null, $customerId, $type);
    }

    public function findPaymentMethod(string $paymentMethod, $customerId = null, $type = 'card'): object
    {
        return (object) $this->preparePaymentMethod($paymentMethod, $customerId);
    }

    public function token(string $token, Customer $customer): object
    {
        return (object) [
            'token_id' => $token,
            'token' => $token,
            'type' => 'card',
            'name' => 'John Doe',
            'exp_month' => '12',
            'exp_year' => '20',
            'last4' => '4242',
            'parent_id' => null,
        ];
    }

    public function oxxo($user): object
    {
        return (object) [
            'type' => 'oxxo',
            'name' => null,
            'exp_month' => null,
            'exp_year' => null,
            'last4' => null,
            'parent_id' => null,
        ];
    }

    public function charge(Customer $customer, PaymentMethod $paymentMethod, array $products, Discount $discount, $address = null, array $options = []): object
    {
        return (object) array_merge(
            $this->preparePayment($customer->asDriver(), $paymentMethod->asDriver(), $products, $discount),
            $this->prepareShipping($address),
        );
    }

    /**
     * @throws WebhookUnavailable
     */
    public function webhook(string $url): void
    {
        throw WebhookUnavailable::exists(static::DRIVER_NAME);
    }

    /**
     * @throws WebhookUnavailable
     */
    public function webhookList(string $url): array
    {
        throw WebhookUnavailable::list(static::DRIVER_NAME);
    }

    /**
     * @throws WebhookUnavailable
     */
    public function webhookDestroy(string $url): void
    {
        throw WebhookUnavailable::destroy(static::DRIVER_NAME);
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

    protected function prepareCustomer(?string $customerId, array $options = []): array
    {
        return [
            'id' => $customerId ?? 'cus_'.Str::random(14),
            'object' => 'customer',
            'address' => null,
            'balance' => 0,
            'created' => Carbon::now()->timestamp,
            'currency' => config('pay.currency'),
            'default_source' => 'card_18NVYR2eZvKYlo2CQ2ieV9S5',
            'delinquent' => false,
            'description' => $options['name'],
            'discount' => null,
            'email' => $options['email'],
            'invoice_prefix' => Str::random(8),
            'invoice_settings' => [
                'custom_fields' => null,
                'default_payment_method' => null,
                'footer' => null,
            ],
            'livemode' => false,
            'name' => $options['name'],
            'phone' => $options['phone'] ?? null,
            'preferred_locales' => [],
            'shipping' => null,
        ];
    }

    protected function prepareShipping($address): array
    {
        if (is_null($address)) {
            return [];
        }

        return [
            'shipping_contact' => [
                'address' => [
                    'street1' => $address->line1,
                    'street2' => $address->line2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                ],
            ],
        ];
    }

    protected function preparePaymentMethod(?string $paymentMethod, $customerId, $type = 'card'): array
    {
        return [
            'id' => $paymentMethod ?? 'src_'.Str::random(17),
            'object' => 'payment_source',
            'type' => $type,
            'created_at' => Carbon::now()->timestamp,
            'last4' => '4242',
            'bin' => '424242',
            'exp_month' => '12',
            'exp_year' => '20',
            'brand' => 'visa',
            'name' => 'John Doe',
            'parent_id' => $customerId,
            'default' => false,
        ];
    }

    protected function preparePayment($customer, $paymentMethod, $products, $discount)
    {
        $id = 'ord_'.Str::random(17);
        $amount = $this->getProductsAmount($products) - $discount->amount;

        return [
            'id' => $id,
            'object' => 'order',
            'amount' => $amount,
            'discount' => $discount->amount,
            'status' => 'paid',
            'currency' => 'MXN',
            'customer_info' => [
                'object' => 'customer_info',
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ],
            'created_at' => Carbon::now()->timestamp,
            'updated_at' => Carbon::now()->timestamp,
            'line_items' => [
                'object' => 'list',
                'total' => Collection::make($products)->sum(fn ($product) => $product->quantity),
                'data' => Collection::make($products)->map(function ($product) use ($id) {
                    return [
                        'id' => 'line_item_'.Str::random(17),
                        'object' => 'line_item',
                        'name' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => $product->quantity,
                        'parent_id' => $id,
                    ];
                })->values()->all(),
            ],
            'charges' => [
                'object' => 'list',
                'data' => [[
                    'id' => Str::random(24),
                    'object' => 'charge',
                    'created_at' => Carbon::now()->timestamp,
                    'status' => 'paid',
                    'amount' => $amount,
                    'discount' => $discount,
                    'currency' => 'MXN',
                    'customer_id' => $paymentMethod->parent_id,
                    'order_id' => $id,
                    'payment_method' => [
                        'object' => 'card_payment',
                        'type' => $paymentMethod->type,
                        'name' => $paymentMethod->name,
                        'exp_month' => $paymentMethod->exp_month,
                        'exp_year' => $paymentMethod->exp_year,
                        'last4' => $paymentMethod->last4,
                        'country' => 'MX',
                    ],
                ]],
            ],
            'oxxo_reference' => Str::random(14),
        ];
    }

    protected function getProductsAmount($products)
    {
        return Collection::make($products)->sum(function ($product) {
            return $product->price * $product->quantity;
        });
    }
}
