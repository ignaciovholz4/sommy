<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            // status_orders_ecommerce usa INT AUTO_INCREMENT → lo dejamos como unsignedInteger
            $table->unsignedInteger('status_order_id')->nullable()->change();

            $table->foreign('status_order_id')
                  ->references('status_id')
                  ->on('status_orders_ecommerce')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            // clientes usa BIGINT UNSIGNED AUTO_INCREMENT → debe ser unsignedBigInteger
            $table->unsignedBigInteger('cliente_id')->nullable()->change();

            $table->foreign('cliente_id')
                  ->references('idcliente')
                  ->on('clientes')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            $table->dropForeign(['status_order_id']);
            $table->dropForeign(['cliente_id']);
        });
    }
};