<?php

namespace Beebmx\LaravelPay;

use Beebmx\LaravelPay\Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        $customer = Pay::$customerModel;

        return $this->belongsTo($customer, (new $customer)->getForeignKey());
    }

    public function asString(): string
    {
        $address[] = "{$this->line1} {$this->line2}";

        if ($this->colony) {
            $address[] = "{$this->colony}";
        }

        if ($this->city) {
            $address[] = "{$this->city}, {$this->state}";
        }

        if ($this->postal_code) {
            $address[] = "{$this->postal_code}";
        }

        if ($this->country) {
            $address[] = "{$this->country}";
        }

        return implode("\n", $address);
    }

    public function asHtml($use_xhtml = true): string
    {
        return nl2br($this->asString(), $use_xhtml);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return AddressFactory::new();
    }
}
