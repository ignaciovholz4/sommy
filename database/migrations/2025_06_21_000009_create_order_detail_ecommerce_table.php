<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_detail_ecommerce', function (Blueprint $table) {
            $table->increments('order_detail_id');
            $table->integer('order_ecommerce_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->tinyInteger('active')->default(1);
            $table->integer('producto_variacion_variante_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_detail_ecommerce');
    }
};
