<?php

namespace Beebmx\LaravelPay\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
