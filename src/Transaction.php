<?php

namespace Beebmx\LaravelPay;

use Beebmx\LaravelPay\Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['items'];

    protected $casts = [
        'user_id' => 'integer',
        'address_id' => 'integer',
        'amount' => 'array',
        'payload' => 'array',
    ];

    public function user()
    {
        $customer = Pay::$customerModel;

        return $this->belongsTo($customer, (new $customer)->getForeignKey());
    }

    public function items()
    {
        return $this->hasMany(Pay::$transactionItemModel);
    }

    public function addItems($products): static
    {
        Collection::make($products)->each(function($product) {
            $this->items()->create([
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $product->quantity,
            ]);
        });

        return $this;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return TransactionFactory::new();
    }
}