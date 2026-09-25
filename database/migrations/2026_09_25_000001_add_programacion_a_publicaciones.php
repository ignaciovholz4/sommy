<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Simplificación del Estudio de Publicaciones (2026-09-25): saca el wizard de
 * 4 pasos y el selector de escena por radios. Agrega:
 * - programado_para / canales_programados: calendario de contenido, un
 *   comando programado publica solo cuando llega la hora.
 * - padre_id: liga una pieza (ej. historia 9:16) a la publicación original
 *   de la que salió, para agruparlas como "el mismo posteo" en la biblioteca.
 * - es_combo: la publicación es de un combo (colchón + relacionados), no de
 *   un producto individual — producto_id sigue siendo el colchón ancla real.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('publicaciones', 'programado_para')) {
                $table->timestamp('programado_para')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('publicaciones', 'canales_programados')) {
                $table->string('canales_programados', 60)->nullable()->after('programado_para');
            }
            if (!Schema::hasColumn('publicaciones', 'padre_id')) {
                $table->unsignedBigInteger('padre_id')->nullable()->index()->after('producto_id');
            }
            if (!Schema::hasColumn('publicaciones', 'es_combo')) {
                $table->boolean('es_combo')->default(false)->after('producto_id');
            }
        });

        // Estilo de imagen por defecto (una sola vez, si el usuario no cargó nada):
        // ambientación homogénea acorde al Manual de Identidad de Sommy, para que
        // todas las piezas generadas mantengan el mismo aire de marca.
        $estiloDefault = 'Dormitorio real prolijo estilo argentino contemporáneo, luz natural de mañana, '
            . 'paleta serena de azul noche, blanco y gris cálido con un toque dorado muy sutil (nunca protagonista), '
            . 'ropa de cama blanca o celeste clara, madera clara, ambiente ordenado sin objetos de sobra, '
            . 'sin personas, estética de fotografía comercial premium y consistente entre publicaciones.';

        DB::table('publicaciones_ajustes')
            ->where(function ($q) {
                $q->whereNull('estilo_imagen')->orWhere('estilo_imagen', '');
            })
            ->update(['estilo_imagen' => $estiloDefault, 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            foreach (['programado_para', 'canales_programados', 'padre_id', 'es_combo'] as $col) {
                if (Schema::hasColumn('publicaciones', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
