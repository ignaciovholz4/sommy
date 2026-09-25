<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de conversaciones del chat del Estudio: cada mensaje del
 * usuario + la respuesta del asistente, para poder repasar qué se pidió
 * y qué contestó la IA (complementa publicaciones_generaciones, que
 * guarda el prompt final de cada imagen).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('publicaciones_conversaciones')) {
            Schema::create('publicaciones_conversaciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('producto_id')->nullable()->index();
                $table->text('mensaje_usuario');
                $table->text('respuesta_asistente')->nullable();
                $table->boolean('genero_imagenes')->default(false);
                $table->boolean('genero_textos')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones_conversaciones');
    }
};
