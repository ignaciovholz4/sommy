<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soporte de carruseles (posts de varias imágenes/slides en Instagram):
 * el primer slide es la fila "contenedora" (es_carrusel=true, slide_orden=0),
 * los siguientes son filas hijas que reusan padre_id (ya existía, ligaba
 * variantes de formato) apuntando a esa fila contenedora.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('publicaciones', 'es_carrusel')) {
                $table->boolean('es_carrusel')->default(false)->after('es_combo');
            }
            if (!Schema::hasColumn('publicaciones', 'slide_orden')) {
                $table->unsignedInteger('slide_orden')->nullable()->after('es_carrusel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            if (Schema::hasColumn('publicaciones', 'slide_orden')) {
                $table->dropColumn('slide_orden');
            }
            if (Schema::hasColumn('publicaciones', 'es_carrusel')) {
                $table->dropColumn('es_carrusel');
            }
        });
    }
};
