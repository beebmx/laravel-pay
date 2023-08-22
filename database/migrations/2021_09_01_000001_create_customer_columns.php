<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('service')->nullable();
            $table->string('service_customer_id')->nullable()->index();
            $table->string('service_payment_id')->nullable();
            $table->string('service_payment_type')->nullable();
            $table->string('service_payment_brand')->nullable();
            $table->string('service_payment_last_four', 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'service',
                'service_customer_id',
                'service_payment_id',
                'service_payment_type',
                'service_payment_brand',
                'service_payment_last_four',
            ]);
        });
    }
}
