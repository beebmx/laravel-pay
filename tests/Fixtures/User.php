<?php

namespace Beebmx\LaravelPay\Tests\Fixtures;

use Beebmx\LaravelPay\Database\Factories\UserFactory;
use Beebmx\LaravelPay\Payable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Payable, HasFactory, Notifiable;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
