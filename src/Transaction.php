<?php

namespace Beebmx\LaravelPay;

use Beebmx\LaravelPay\Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['items'];

    protected $casts = [
        'user_id' => 'integer',
        'address_id' => 'integer',
        'amount' => 'double',
        'total' => 'double',
        'discount' => 'double',
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        $customer = Pay::$customerModel;

        return $this->belongsTo($customer, (new $customer)->getForeignKey());
    }

    public function items(): HasMany
    {
        return $this->hasMany(Pay::$transactionItemModel);
    }

    public function addItems($products): static
    {
        Collection::make($products)->each(function ($product) {
            $this->items()->create([
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'model_id' => $product->model,
            ]);
        });

        return $this;
    }

    public function paid()
    {
        return $this->forceFill([
            'status' => 'paid',
        ])->save();
    }

    public function canceled()
    {
        return $this->forceFill([
            'status' => 'canceled',
        ])->save();
    }

    public function changeStatus(string $status)
    {
        return $this->forceFill([
            'status' => $status,
        ])->save();
    }

    public static function findByPaymentId(string $payment_id)
    {
        return self::query()
            ->where('service_payment_id', $payment_id)
            ->first();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return TransactionFactory::new();
    }
}
