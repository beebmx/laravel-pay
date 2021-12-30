<?php

namespace Beebmx\LaravelPay\Tests\Feature;

use Beebmx\LaravelPay\Events\WebhookHandled;
use Beebmx\LaravelPay\Events\WebhookReceived;
use Beebmx\LaravelPay\Pay;
use Beebmx\LaravelPay\Tests\FeatureTestCase;
use Illuminate\Support\Facades\Event;

class WebhooksEventTest extends FeatureTestCase
{
    /** @test */
    public function a_webhook_always_dispatch_webhook_received()
    {
        Event::fake([WebhookReceived::class]);

        $request = $this->request(Pay::getWebhookspath());
        app()->handle($request);

        Event::assertDispatched(WebhookReceived::class);
    }

    /** @test */
    public function a_webhook_not_dispatch_webhook_handled_if_its_not_handled()
    {
        Event::fake([WebhookHandled::class]);

        $this->assertEquals('sandbox', config('pay.default'));

        $request = $this->request(Pay::getWebhookspath());
        app()->handle($request);

        Event::assertNotDispatched(WebhookHandled::class);
    }

    /**
     * @test
     * @define-env useConektaDriver
     */
    public function a_webhook_dispatch_webhook_handled_if_its_handled()
    {
        Event::fake([WebhookHandled::class]);

        $this->assertEquals('conekta', config('pay.default'));

        $request = $this->request(Pay::getWebhookspath(), ['id' => 'ord_0123456789', 'type' => 'order.paid', 'data' => ['object' => ['status' => 'paid']]]);
        app()->handle($request);

        Event::assertDispatched(WebhookHandled::class);
    }

    protected function useConektaDriver($app)
    {
        $app['config']->set('pay.default', 'conekta');
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.url', 'http://laravel-pay.test');
    }
}
