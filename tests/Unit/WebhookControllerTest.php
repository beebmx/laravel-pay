<?php

namespace Beebmx\LaravelPay\Tests\Unit;

use Beebmx\LaravelPay\Http\Controllers\WebhooksController;
use PHPUnit\Framework\TestCase;

class WebhookControllerTest extends TestCase
{
    /** @test */
    public function it_converts_an_event_to_a_method_name()
    {
        $method = (new WebhooksController)->eventToMethod('order.paid');

        $this->assertEquals('handleOrderPaid', $method);
    }
}
