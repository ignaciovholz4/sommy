<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prioridad de uso de cada item de conocimiento: le permite al dueño marcar
 * qué foto/video/texto tiene que usar el bot primero al presentar un producto
 * (ej: la foto principal del ecommerce, el video destacado).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('articulo_conocimiento', 'prioridad')) {
            Schema::table('articulo_conocimiento', function (Blueprint $table) {
                $table->unsignedTinyInteger('prioridad')->default(0)->after('activo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('articulo_conocimiento', 'prioridad')) {
            Schema::table('articulo_conocimiento', function (Blueprint $table) {
                $table->dropColumn('prioridad');
            });
        }
    }
};
