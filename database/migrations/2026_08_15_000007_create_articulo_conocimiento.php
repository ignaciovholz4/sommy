<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Base de conocimiento INTERNA por producto (no se muestra en el ecommerce):
 * instrucciones, características, FAQs y archivos (imágenes, videos, audios)
 * que explican el producto. La usan como contexto el bot del CRM y el
 * Estudio de Publicaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('articulo_conocimiento')) {
            Schema::create('articulo_conocimiento', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('articulo_id')->index();
                $table->enum('tipo', ['instrucciones', 'caracteristicas', 'faq', 'nota', 'imagen', 'video', 'audio', 'documento']);
                $table->string('titulo', 150);
                $table->text('contenido')->nullable();  // texto del conocimiento
                $table->string('archivo', 255)->nullable();
                $table->string('mime', 100)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('articulo_conocimiento');
    }
};
