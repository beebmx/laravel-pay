<?php

namespace Beebmx\LaravelPay\Tests\Feature;

use Beebmx\LaravelPay\Tests\FeatureTestCase;
use Illuminate\Support\Facades\DB;

class PayTest extends FeatureTestCase
{
    /** @test */
    public function it_runs_pay_migrations()
    {
        $migrations = DB::table('migrations')->get();

        $this->assertTrue(
            $migrations->contains('migration', '2021_09_01_000001_create_customer_columns')
        );
        $this->assertTrue(
            $migrations->contains('migration', '2021_09_01_000002_create_addresses_table')
        );
        $this->assertTrue(
            $migrations->contains('migration', '2021_09_01_000003_create_transactions_table')
        );
        $this->assertTrue(
            $migrations->contains('migration', '2021_09_01_000004_create_transaction_items_table')
        );
    }
}
