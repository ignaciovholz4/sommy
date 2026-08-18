<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Publicaciones generadas en el Estudio de Publicaciones:
 * copys de IA, escena generada, imagen final compuesta y estado de publicacion.
 * (publicaciones_registro queda como log liviano de "marcado como publicado").
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('publicaciones')) {
            Schema::create('publicaciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('producto_id')->index();
                $table->string('formato', 20)->default('post');       // ml | post | story
                $table->string('estilo', 30)->nullable();             // claro | noche | escena IA elegida
                $table->string('titulo_ml', 120)->nullable();
                $table->text('desc_ml')->nullable();
                $table->text('caption')->nullable();                  // Instagram / Facebook
                $table->text('texto_wa')->nullable();                 // mensaje corto WhatsApp
                $table->string('imagen_escena', 255)->nullable();     // fondo/escena generada por IA
                $table->string('imagen_final', 255)->nullable();      // PNG compuesto (canvas)
                $table->text('prompt_escena')->nullable();
                $table->string('estado', 20)->default('borrador');    // borrador | publicada
                $table->string('fb_post_id', 60)->nullable();
                $table->string('ig_post_id', 60)->nullable();
                $table->timestamp('publicada_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
};
