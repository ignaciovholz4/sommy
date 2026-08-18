<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite asociar una imagen de la galería a una combinación (variante) puntual.
     * Las filas con combinacion_id NULL siguen siendo imágenes generales del producto.
     */
    public function up(): void
    {
        Schema::table('producto_imagenes', function (Blueprint $table) {
            $table->unsignedBigInteger('combinacion_id')->nullable()->after('producto_id');
            $table->foreign('combinacion_id')
                ->references('idcombinacion')
                ->on('producto_combinaciones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('producto_imagenes', function (Blueprint $table) {
            $table->dropForeign(['combinacion_id']);
            $table->dropColumn('combinacion_id');
        });
    }
};
