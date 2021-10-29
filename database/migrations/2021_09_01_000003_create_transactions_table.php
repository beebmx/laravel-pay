<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('service');
            $table->string('service_id');
            $table->string('service_type');
            $table->string('service_payment_id')->unique();
            $table->string('address_id')->nullable();
            $table->string('amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('shipping')->nullable();
            $table->string('status');
            $table->json('payload')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
