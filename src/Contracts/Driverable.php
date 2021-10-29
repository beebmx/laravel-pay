<?php

namespace Beebmx\LaravelPay\Contracts;

use Beebmx\LaravelPay\Elements\Customer;
use Beebmx\LaravelPay\Elements\PaymentMethod;

interface Driverable
{
    public static function urls(): array;

    public function name(): string;

    public function customerCreate(array $options = []): object;

    public function asCustomer(string $customerId, array $options = []): object;

    public function addPaymentMethod($paymentMethod, $customerId = null, $type = 'card'): object;

    public function findPaymentMethod(string $paymentMethod, $customerId = null, $type = 'card'): object;

    public function token(string $token): object;

    /**
     * @param Customer $customer
     * @param PaymentMethod $paymentMethod
     * @param array $products
     * @param \Beebmx\LaravelPay\Address|null $address
     * @param array $options
     * @return object
     */
    public function charge(Customer $customer, PaymentMethod $paymentMethod, array $products, $address = null, $options = []): object;

    public function preparePrice($amount): int|float;

    public function parsePrice($amount): float;

    public static function getPaymentType($paymentMethod);

    public static function getPaymentBrand($paymentMethod);

    public static function getPaymentLastFour($paymentMethod);
}
