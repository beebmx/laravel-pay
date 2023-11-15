<?php

namespace Beebmx\LaravelPay;

class Pay
{
    const VERSION = '0.6.1';

    public static bool $runsMigrations = true;

    public static bool $registersRoutes = true;

    public static $customerModel = 'App\\Models\\User';

    public static $addressModel = Address::class;

    public static $transactionModel = Transaction::class;

    public static $transactionItemModel = TransactionItem::class;

    /**
     * Disable migrations for Pay
     */
    public static function ignoreMigrations(): static
    {
        static::$runsMigrations = false;

        return new static;
    }

    public static function useCustomerModel($customerModel): void
    {
        static::$customerModel = $customerModel;
    }

    public static function useAddressModel($addressModel): void
    {
        static::$addressModel = $addressModel;
    }

    public static function useTransactionModel($transactionModel): void
    {
        static::$transactionModel = $transactionModel;
    }

    public static function useTransactionItemModel($transactionItemModel): void
    {
        static::$transactionItemModel = $transactionItemModel;
    }

    public static function getWebhookspath(): string
    {
        return config('pay.path').'/'.config('pay.webhooks');
    }
}
