<?php

namespace Beebmx\LaravelPay\Elements;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;

abstract class Element implements Arrayable, Jsonable, JsonSerializable
{
    use Macroable;

    public function __construct(protected $user, protected $driverElement)
    {
    }

    public function asDriver(): object
    {
        return $this->driverElement;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toJson($options = 0): bool|string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function toArray(): array
    {
        return (array) $this->driverElement;
    }

    public function __get($key)
    {
        return $this->driverElement->{$key} ?? null;
    }
}
