<?php

namespace Beebmx\LaravelPay\Drivers;

use Beebmx\LaravelPay\Contracts\Driverable;
use Illuminate\Support\Traits\Conditionable;

abstract class Driver implements Driverable
{
    use Conditionable;

    const DRIVER_NAME = '';

    protected string $version = '1.0.0';

    protected int $unit = 1;

    public function __construct()
    {
        $this->boot();
    }

    abstract protected function boot();

    public function name(): string
    {
        return static::DRIVER_NAME;
    }

    /**
     * Parse the amount for the service
     *
     * @param $amount
     * @return int|float
     */
    public function preparePrice($amount): int|float
    {
        return (float) $amount * $this->unit;
    }

    /**
     * Get the real amount
     *
     * @param $amount
     * @return float
     */
    public function parsePrice($amount): float
    {
        return (int) $amount / $this->unit;
    }

    public function getName(): string
    {
        return static::DRIVER_NAME;
    }
}
