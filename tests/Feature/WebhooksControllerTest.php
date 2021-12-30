<?php

namespace Beebmx\LaravelPay\Tests\Feature;

use Beebmx\LaravelPay\Pay;
use Beebmx\LaravelPay\Tests\TestCase;

class WebhooksControllerTest extends TestCase
{
    /** @test */
    public function a_webhook_controller_always_returns_a_response()
    {
        $request = $this->request(Pay::getWebhookspath());
        $response = app()->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }
}
