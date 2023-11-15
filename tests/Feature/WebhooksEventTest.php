<?php

use Beebmx\LaravelPay\Events\WebhookHandled;
use Beebmx\LaravelPay\Events\WebhookReceived;
use Beebmx\LaravelPay\Pay;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    config(['app.url' => 'http://laravel-pay.test']);
});

test('a webhook always dispatch webhook received', function () {
    Event::fake([WebhookReceived::class]);

    $request = $this->request(Pay::getWebhookspath());
    app()->handle($request);

    Event::assertDispatched(WebhookReceived::class);
});

test('a webhook not dispatch webhook handled if its not handled', function () {
    Event::fake([WebhookHandled::class]);

    expect(config('pay.default'))
        ->toEqual('sandbox');

    $request = $this->request(Pay::getWebhookspath());
    app()->handle($request);

    Event::assertNotDispatched(WebhookHandled::class);
});

test('a webhook dispatch webhook handled if its handled', function () {
    config(['pay.default' => 'conekta']);
    Event::fake([WebhookHandled::class]);

    expect(config('pay.default'))
        ->toEqual('conekta');

    $request = $this->request(Pay::getWebhookspath(), ['id' => 'ord_0123456789', 'type' => 'order.paid', 'data' => ['object' => ['status' => 'paid']]]);
    app()->handle($request);

    Event::assertDispatched(WebhookHandled::class);
});
