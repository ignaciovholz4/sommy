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
        Schema::table('detalle_ventas', function (Blueprint $table) {
            // ✅ combinacion_id puede ser null
            $table->unsignedBigInteger('combinacion_id')->nullable()->after('articulo_id');
            // ✅ tipo_producto_id obligatorio, sin foreign
            $table->unsignedBigInteger('tipo_producto_id')->after('combinacion_id');

            $table->foreign('combinacion_id')
                  ->references('idcombinacion')
                  ->on('producto_combinaciones')
                  ->onDelete('set null');
        });

        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->unsignedBigInteger('combinacion_id')->nullable()->after('articulo_id');
            $table->unsignedBigInteger('tipo_producto_id')->after('combinacion_id');

            $table->foreign('combinacion_id')
                  ->references('idcombinacion')
                  ->on('producto_combinaciones')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropForeign(['combinacion_id']);
            $table->dropColumn(['combinacion_id', 'tipo_producto_id']);
        });

        Schema::table('detalle_compras', function (Blueprint $table) {
            $table->dropForeign(['combinacion_id']);
            $table->dropColumn(['combinacion_id', 'tipo_producto_id']);
        });
    }
};