<?php

namespace Beebmx\LaravelPay\Tests;

use Beebmx\LaravelPay\Pay;
use Beebmx\LaravelPay\PayServiceProvider;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\RayServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PayServiceProvider::class,
            RayServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        Pay::useCustomerModel(User::class);
    }
}
