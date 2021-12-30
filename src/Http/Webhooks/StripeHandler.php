<?php

namespace Beebmx\LaravelPay\Http\Webhooks;

use Beebmx\LaravelPay\Concerns\HasWebhookHandler;

class StripeHandler
{
    use HasWebhookHandler;

    public function handlePaymentIntentSucceeded(array $payload)
    {
        $status = $this->getPayloadStatus($payload);
        $transaction = $this->find($this->getPaymentId($payload));

        if ($status === 'succeeded' || $status === 'paid') {
            $transaction?->paid();
        } else {
            $transaction?->changeStatus($status);
        }
    }

    public function handlePaymentIntentCanceled(array $payload)
    {
        $transaction = $this->find($this->getPaymentId($payload));
        $transaction?->canceled();
    }

    protected function getPaymentId(array $payload): string
    {
        return $payload['data']['object']['id']
            ?? $payload['id'];
    }

    protected function getPayloadStatus(array $payload)
    {
        return $payload['data']['object']['status']
            ?? $payload['data']['object']['charges']['data'][0]['status'];
    }
}
