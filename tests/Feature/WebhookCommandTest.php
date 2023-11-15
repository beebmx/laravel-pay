<?php

use Beebmx\LaravelPay\Exceptions\WebhookUnavailable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['app.url' => 'http://laravel-pay.test']);
});

test('a webhook command takes the default driver', function () {
    $this->expectException(WebhookUnavailable::class);

    $this->artisan('pay:webhooks')
        ->expectsConfirmation('Do you really want to create a webhook for sandbox driver?', 'yes');
});

test('a webhook command can not list in sandbox', function () {
    $this->expectException(WebhookUnavailable::class);

    $this->artisan('pay:webhooks --list');
});

test('a webhook command can not destroy in sandbox', function () {
    $this->expectException(WebhookUnavailable::class);

    $this->artisan('pay:webhooks --destroy')
        ->expectsConfirmation('Do you really want to destroy all webhooks for sandbox driver?', 'yes');
});

test('a webhook command its not available for sandbox', function () {
    $this->expectException(WebhookUnavailable::class);

    $this->artisan('pay:webhooks --driver=sandbox')
        ->expectsConfirmation('Do you really want to create a webhook for sandbox driver?', 'yes');
});

test('a webhook command for conekta creates an endpoint', function () {
    config(['pay.default' => 'conekta']);

    $this->artisan('pay:webhooks')
        ->expectsConfirmation('Do you really want to create a webhook for conekta driver?', 'no')
        ->assertExitCode(Command::FAILURE);
});

test('a webhook command list all the webhooks for conekta driver', function () {
    config(['pay.default' => 'conekta']);

    Http::fake([
        'api.conekta.io/*' => Http::response(['data' => conektaListStubs()], 200),
    ]);

    $this->artisan('pay:webhooks --list')
        ->expectsTable(['ID', 'Status', 'URL', 'Production'], [[
            'id' => 'webhook_0123456789',
            'status' => 'connected',
            'url' => 'http://laravel-pay.test/pay/webhooks',
            'production' => '❎',
        ]])->assertExitCode(Command::SUCCESS);
});

test('a webhook command for conekta destroy all endpoints', function () {
    config(['pay.default' => 'conekta']);

    Http::fake([
        'api.conekta.io/*' => Http::response(['data' => conektaListStubs()], 200),
    ]);

    $this->artisan('pay:webhooks --destroy')
        ->expectsConfirmation('Do you really want to destroy all webhooks for conekta driver?', 'yes')
        ->assertExitCode(Command::SUCCESS);
});

test('a webhook command for stripe creates an endpoint', function () {
    config(['pay.default' => 'stripe']);

    $this->artisan('pay:webhooks')
        ->expectsConfirmation('Do you really want to create a webhook for stripe driver?', 'no')
        ->assertExitCode(Command::FAILURE);
});

test('a webhook command list all the webhooks for stripe driver', function () {
    config(['pay.default' => 'stripe']);

    $this->artisan('pay:webhooks --list')
        ->expectsTable(['ID', 'Status', 'URL', 'Production'], [])
        ->assertExitCode(Command::SUCCESS);
});

test('a webhook command for stripe destroy all endpoints', function () {
    config(['pay.default' => 'stripe']);

    $this->artisan('pay:webhooks --destroy')
        ->expectsConfirmation('Do you really want to destroy all webhooks for stripe driver?', 'yes')
        ->assertExitCode(Command::SUCCESS);
});

function conektaListStubs(): array
{
    return [[
        'id' => 'webhook_0123456789',
        'status' => 'connected',
        'url' => 'http://laravel-pay.test/pay/webhooks',
        'production_enabled' => false,
    ]];
}

function stripeListStubs(): array
{
    return [[
        'id' => 'webhook_0123456789',
        'status' => 'connected',
        'url' => 'http://laravel-pay.test/pay/webhooks',
        'production_enabled' => false,
    ]];
}
