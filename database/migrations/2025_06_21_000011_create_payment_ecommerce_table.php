<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_ecommerce', function (Blueprint $table) {
            $table->increments('payment_id');
            $table->integer('order_id')->nullable();
            $table->integer('payment_method_id')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->string('status_payment')->nullable();
            $table->tinyInteger('status')->default(1);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_ecommerce');
    }
};
