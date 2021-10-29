<?php

namespace Beebmx\LaravelPay;

use Beebmx\LaravelPay\Database\Factories\TransactionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'transaction_id' => 'integer',
        'quantity' => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return TransactionItemFactory::new();
    }
}