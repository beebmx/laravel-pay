<?php

namespace Beebmx\LaravelPay\Tests;

use Beebmx\LaravelPay\Pay;
use Beebmx\LaravelPay\PayServiceProvider;
use Beebmx\LaravelPay\Tests\Fixtures\User;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\RayServiceProvider;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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

    protected function request($uri, array $content = []): Request
    {
        $request = SymfonyRequest::create(
            $uri,
            $method = 'POST',
            $parameters = [],
            $cookies = [],
            $files = [],
            $server = [],
            $content = json_encode($content)
        );

        return Request::createFromBase($request);
    }
}
