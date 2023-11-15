<?php

use Beebmx\LaravelPay\PurchaseItem;

test('a quantity of purchase is optional', function () {
    $product = new PurchaseItem('Product name', 500);

    expect($product->quantity)
        ->toEqual(1);
});

test('a quantity of purchase can be set', function () {
    $product = new PurchaseItem('Product name', 500, 5);

    expect($product->quantity)
        ->toEqual(5);
});
