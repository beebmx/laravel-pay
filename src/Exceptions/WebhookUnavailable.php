<?php

namespace Beebmx\LaravelPay\Exceptions;

use Exception;

class WebhookUnavailable extends Exception
{
    public static function exists(string $driver): static
    {
        return new static("The webhook for driver {$driver} is unavailable.");
    }

    public static function list(string $driver): static
    {
        return new static("The webhook for driver {$driver} can not list webhooks.");
    }

    public static function destroy(string $driver): static
    {
        return new static("The webhook for driver {$driver} can not destroy webhooks.");
    }
}
