<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_stock_asignaciones', function (Blueprint $table) {
            $table->id();

            // 🔹 Relación con la orden (INT UNSIGNED)
            $table->unsignedInteger('order_id');
            $table->foreign('order_id')
                  ->references('order_id')
                  ->on('order_ecommerce')
                  ->onDelete('cascade');

            // 🔹 Relación con el producto (BIGINT UNSIGNED)
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')
                  ->references('idarticulo')
                  ->on('productos')
                  ->onDelete('cascade');

            // 🔹 Relación con la combinación (BIGINT UNSIGNED, nullable)
            $table->unsignedBigInteger('combinacion_id')->nullable();
            $table->foreign('combinacion_id')
                  ->references('idcombinacion')
                  ->on('producto_combinaciones')
                  ->onDelete('cascade');

            // 🔹 Relación con la sucursal (BIGINT UNSIGNED)
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')
                  ->references('id')
                  ->on('sucursales')
                  ->onDelete('cascade');

            // 🔹 Cantidad asignada
            $table->integer('cantidad');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_stock_asignaciones');
    }
};
