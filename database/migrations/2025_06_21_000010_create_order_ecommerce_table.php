<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_ecommerce', function (Blueprint $table) {
            $table->increments('order_id');
            $table->integer('status_order_id')->nullable();
            $table->integer('cliente_id')->nullable();
            $table->dateTime('order_date')->nullable();
            $table->decimal('subtotal_amount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->tinyInteger('active')->default(1);
            $table->text('additional_info')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_ecommerce');
    }
};
