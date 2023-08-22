<?php

namespace Beebmx\LaravelPay\Http\Controllers;

use Beebmx\LaravelPay\Events\WebhookHandled;
use Beebmx\LaravelPay\Events\WebhookReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WebhooksController
{
    public function __invoke(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);

        WebhookReceived::dispatch($payload);

        if ($this->hasHandler()) {
            $handler = new ($this->getCurrentHandler());

            if (method_exists($handler, $method = $this->eventToMethod($payload['type'] ?? null))) {
                $handler->$method($payload);

                WebhookHandled::dispatch($payload);

                return $this->successMethod($payload);
            }
        }

        return $this->missingMethod($payload);
    }

    public function eventToMethod($event)
    {
        return 'handle'.Str::studly(str_replace('.', '_', $event));
    }

    protected function successMethod($payload = [])
    {
        return new Response('Webhook Received', 200);
    }

    protected function missingMethod($payload = []): Response
    {
        return new Response;
    }

    protected function hasHandler(): bool
    {
        return (bool) $this->getCurrentHandler();
    }

    protected function getCurrentWebhookDriver(): ?array
    {
        return config('pay.drivers')[config('pay.default')]['webhooks'] ?? null;
    }

    protected function getCurrentHandler()
    {
        $webhook = $this->getCurrentWebhookDriver() ?? [];

        return array_key_exists('handler', $webhook)
            ? $webhook['handler']
            : null;
    }
}
