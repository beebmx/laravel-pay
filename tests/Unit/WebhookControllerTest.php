<?php

use Beebmx\LaravelPay\Http\Controllers\WebhooksController;

it('converts an event to a method name', function () {
    $method = (new WebhooksController)->eventToMethod('order.paid');

    expect($method)
        ->toEqual('handleOrderPaid');
});
