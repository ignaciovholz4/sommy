<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Guarda las imágenes generadas en cada intercambio del chat, para poder verlas directo desde el Historial. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones_conversaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('publicaciones_conversaciones', 'imagenes_json')) {
                $table->text('imagenes_json')->nullable()->after('genero_textos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones_conversaciones', function (Blueprint $table) {
            if (Schema::hasColumn('publicaciones_conversaciones', 'imagenes_json')) {
                $table->dropColumn('imagenes_json');
            }
        });
    }
};
