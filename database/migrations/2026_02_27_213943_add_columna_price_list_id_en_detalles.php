<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Detalle de ventas
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('price_list_id')->nullable()->after('tipo_producto_id');
            $table->foreign('price_list_id')
                  ->references('id')
                  ->on('price_lists')
                  ->onDelete('set null');
        });

        // Detalle de presupuestos
        Schema::table('detalles_presupuesto', function (Blueprint $table) {
            $table->unsignedBigInteger('price_list_id')->nullable()->after('tipo_producto_id');
            $table->foreign('price_list_id')
                  ->references('id')
                  ->on('price_lists')
                  ->onDelete('set null');
        });

        // Detalle de compras
        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->unsignedBigInteger('price_list_id')->nullable()->after('tipo_producto_id');
            $table->foreign('price_list_id')
                  ->references('id')
                  ->on('price_lists')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropForeign(['price_list_id']);
            $table->dropColumn('price_list_id');
        });

        Schema::table('detalles_presupuesto', function (Blueprint $table) {
            $table->dropForeign(['price_list_id']);
            $table->dropColumn('price_list_id');
        });

        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->dropForeign(['price_list_id']);
            $table->dropColumn('price_list_id');
        });
    }
};