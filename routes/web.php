<?php

use Beebmx\LaravelPay\Http\Controllers\WebhooksController;
use Illuminate\Support\Facades\Route;

Route::post(config('pay.webhooks'), WebhooksController::class)->name('webhook');
