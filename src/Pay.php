<?php

namespace Beebmx\LaravelPay;

class Pay
{
    const VERSION = '0.1.0';

    public static $runsMigrations = true;

    public static $customerModel = 'App\\Models\\User';

    public static $addressModel = Address::class;

    public static $transactionModel = Transaction::class;

    public static $transactionItemModel = TransactionItem::class;

    /**
     * Disable migrations for Pay
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }

    public static function useCustomerModel($customerModel)
    {
        static::$customerModel = $customerModel;
    }

    public static function useAddressModel($addressModel)
    {
        static::$addressModel = $addressModel;
    }

    public static function useTransactionModel($transactionModel)
    {
        static::$transactionModel = $transactionModel;
    }

    public static function useTransactionItemModel($transactionItemModel)
    {
        static::$transactionItemModel = $transactionItemModel;
    }
}
