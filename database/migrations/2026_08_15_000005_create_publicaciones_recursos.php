<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Biblioteca de recursos de marca del Estudio de Publicaciones:
 * - imagen / logo: archivos para usar en piezas
 * - prompt: indicaciones guardadas para generar escenas
 * - contexto: información de la marca que la IA usa al escribir
 *   (datos del negocio, direcciones, promociones vigentes, etc.)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('publicaciones_recursos')) {
            Schema::create('publicaciones_recursos', function (Blueprint $table) {
                $table->id();
                $table->enum('tipo', ['imagen', 'logo', 'prompt', 'contexto']);
                $table->string('titulo', 120);
                $table->text('contenido')->nullable();   // prompt o texto de contexto
                $table->string('archivo', 255)->nullable(); // imagen o logo
                $table->boolean('activo')->default(true);   // los contextos activos se inyectan a la IA
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones_recursos');
    }
};
