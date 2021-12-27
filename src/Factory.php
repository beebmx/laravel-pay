<?php

namespace Beebmx\LaravelPay;

use Beebmx\LaravelPay\Exceptions\DriverNonInstantiable;
use Beebmx\LaravelPay\Exceptions\InvalidDriver;

class Factory
{
    /**
     * @throws DriverNonInstantiable
     * @throws InvalidDriver
     */
    public static function make(string $service = null)
    {
        $service = is_null($service)
            ? self::getService()
            : self::getServiceByName($service);

        if (! self::isInstantiable($service)) {
            throw DriverNonInstantiable::invalid($service);
        }

        return $service;
    }

    /**
     * @throws InvalidDriver
     */
    public static function getService()
    {
        if (! array_key_exists(static::getDefaultService(), static::getDrivers())) {
            throw InvalidDriver::exists(static::getDefaultService());
        }

        return static::getDrivers()[static::getDefaultService()]['driver'] ?? null;
    }

    /**
     * @throws InvalidDriver
     */
    public static function getServiceByName(string $service)
    {
        if (! array_key_exists($service, static::getDrivers())) {
            throw InvalidDriver::exists($service);
        }

        return static::getDrivers()[$service]['driver'] ?? null;
    }

    public static function getDriversClasses(): array
    {
        return collect(self::getDrivers())
            ->map(fn($driver) => $driver['driver'])
            ->toArray();
    }

    public static function getDefaultService(): string
    {
        return config('pay.default');
    }

    protected static function getDrivers(): array
    {
        return config('pay.drivers');
    }

    protected static function isInstantiable($class): bool
    {
        return class_exists($class);
    }
}