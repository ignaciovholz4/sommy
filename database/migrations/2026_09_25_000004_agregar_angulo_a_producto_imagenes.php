<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sommy Creative Studio: permite etiquetar el ángulo de cada foto de la
 * galería real del producto (producto_imagenes), para que el Product
 * Library pueda distinguir frontal/lateral/3-4/detalle/superior al elegir
 * la imagen de referencia principal para generar una escena.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_imagenes', function (Blueprint $table) {
            if (!Schema::hasColumn('producto_imagenes', 'angulo')) {
                $table->string('angulo', 20)->nullable()->after('tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('producto_imagenes', function (Blueprint $table) {
            if (Schema::hasColumn('producto_imagenes', 'angulo')) {
                $table->dropColumn('angulo');
            }
        });
    }
};
