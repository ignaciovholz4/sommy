<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot crudo de la respuesta de la Data Export API de Microsoft Clarity
 * (un array de {metricName, information[]} — Microsoft no documenta el shape
 * exacto de cada metrica, asi que se guarda entero y se renderiza generico).
 * Una fila por dia de sincronizacion (la API limita a 10 llamadas/dia por
 * proyecto, asi que no se sincroniza en cada carga de pantalla).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clarity_insights', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->json('payload');
            $table->timestamp('sincronizado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clarity_insights');
    }
};
