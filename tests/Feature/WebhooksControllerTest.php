<?php

use Beebmx\LaravelPay\Pay;

test('a webhook controller always returns a response', function () {
    $request = $this->request(Pay::getWebhookspath());
    $response = app()->handle($request);

    expect($response->getStatusCode())
        ->toEqual(200);
});
