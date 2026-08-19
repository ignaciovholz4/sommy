<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Configuracion de la reposicion inteligente de stock: una sola fila.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reposicion_ajustes')) {
            Schema::create('reposicion_ajustes', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('dias_cobertura_objetivo')->default(30);
                $table->unsignedInteger('ventana_analisis_dias')->default(60);
                $table->unsignedInteger('stock_minimo_default')->default(3);
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            DB::table('reposicion_ajustes')->insert([
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reposicion_ajustes');
    }
};
