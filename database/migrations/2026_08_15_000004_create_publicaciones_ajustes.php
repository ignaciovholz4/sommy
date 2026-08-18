<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Entrenamiento" del Estudio de Publicaciones: una sola fila con el estilo
 * que el usuario definió para su marca. La IA lo usa en cada generación.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('publicaciones_ajustes')) {
            Schema::create('publicaciones_ajustes', function (Blueprint $table) {
                $table->id();
                $table->text('voz_marca')->nullable();      // cómo escribe la marca (textos/captions)
                $table->text('estilo_imagen')->nullable();  // estética fotográfica de las escenas
                $table->timestamps();
            });

            DB::table('publicaciones_ajustes')->insert([
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones_ajustes');
    }
};
