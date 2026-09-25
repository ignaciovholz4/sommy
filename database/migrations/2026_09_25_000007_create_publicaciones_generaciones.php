<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de generaciones de IA (reproducibilidad): guarda el prompt final
 * exacto, el modelo y el resultado de cada llamada a Gemini, se haya usado
 * desde el chat o desde el modo Create.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('publicaciones_generaciones')) {
            Schema::create('publicaciones_generaciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('producto_id')->nullable()->index();
                $table->string('modelo', 60)->nullable();
                $table->string('formato', 20)->nullable();
                $table->text('prompt_final')->nullable();
                $table->string('estado', 20)->default('ok'); // ok | error
                $table->text('error')->nullable();
                $table->string('imagen_path', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('publicaciones_generaciones');
    }
};
