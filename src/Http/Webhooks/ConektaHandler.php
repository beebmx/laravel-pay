<?php

namespace Beebmx\LaravelPay\Http\Webhooks;

use Beebmx\LaravelPay\Concerns\HasWebhookHandler;

class ConektaHandler
{
    use HasWebhookHandler;

    public function handleOrderPaid(array $payload)
    {
        $transaction = $this->find($this->getPaymentId($payload));

        $transaction?->paid();
    }

    public function handleOrderCanceled(array $payload)
    {
        $transaction = $this->find($this->getPaymentId($payload));

        $transaction?->canceled();
    }

    protected function getPaymentId(array $payload): string
    {
        return $payload['data']['object']['id']
            ?? $payload['id'];
    }

    protected function getPayloadStatus(array $payload): ?string
    {
        return $payload['data']['object']['status']
            ?? $payload['payment_status'];
    }
}
