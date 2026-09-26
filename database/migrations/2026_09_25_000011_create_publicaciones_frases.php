<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Banco de frases pre-escritas para titulares/banners. En vez de que Gemini
 * "invente" el texto del banner cada vez (con riesgo real de que lo escriba
 * mal, ej. "SALIR DE DE LA CAMA"), se elige una frase de acá — ya revisada y
 * bien escrita — y se le pide a la IA que la copie EXACTO, letra por letra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicaciones_frases', function (Blueprint $table) {
            $table->id();
            $table->string('categoria', 40); // comercial, divertido, familiar, emotivo, educativo, marca, estacional
            $table->string('texto', 160);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones_frases');
    }
};
