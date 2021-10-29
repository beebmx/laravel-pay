<?php

namespace Beebmx\LaravelPay\Concerns;

use Beebmx\LaravelPay\Exceptions\InvalidDriver;
use Beebmx\LaravelPay\Factory;

trait ManagesFactories
{
    /**
     * @throws InvalidDriver
     */
    public function getCustomerDriver()
    {
        if (!$this->service) {
            throw InvalidDriver::customerRequireDriver($this);
        }

        return Factory::getDriversClasses()[$this->service];
    }

    /**
     * @throws \Beebmx\LaravelPay\Exceptions\DriverNonInstantiable
     * @throws InvalidDriver
     */
    public static function driver()
    {
        return new (Factory::make());
    }

    public function getDefaultDriverName(): string
    {
        return Factory::getDefaultService();
    }
}
