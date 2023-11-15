<?php

use Illuminate\Support\Facades\DB;

it('runs pay migrations', function () {
    $migrations = DB::table('migrations')->get();

    expect($migrations->contains('migration', '2021_09_01_000001_create_customer_columns'))->toBeTrue()
        ->and($migrations->contains('migration', '2021_09_01_000002_create_addresses_table'))->toBeTrue()
        ->and($migrations->contains('migration', '2021_09_01_000003_create_transactions_table'))->toBeTrue()
        ->and($migrations->contains('migration', '2021_09_01_000004_create_transaction_items_table'))->toBeTrue();
});
