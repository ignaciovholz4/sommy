<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto_variacion_variante', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('color_id')->nullable();
            $table->integer('product_integration_id')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('name_image')->nullable();
            $table->string('path_image')->nullable();
            $table->decimal('stock', 15, 2)->nullable();
            $table->tinyInteger('active')->default(1);
            $table->timestamp('registration_date')->nullable()->useCurrent();
            $table->tinyInteger('show_ecommerce')->default(0);
            $table->decimal('pcompra', 15, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto_variacion_variante');
    }
};
