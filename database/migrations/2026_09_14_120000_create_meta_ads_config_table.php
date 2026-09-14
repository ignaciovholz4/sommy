<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ads_config', function (Blueprint $table) {
            $table->id();
            // Topes de gasto para el modulo de gestion de campanas (Fase 4): si una accion
            // de escritura (crear campana, subir presupuesto) supera el tope, se rechaza
            // antes de llegar a Meta y antes de generar una solicitud de aprobacion.
            $table->decimal('tope_presupuesto_diario', 12, 2)->nullable();
            $table->decimal('tope_presupuesto_total', 12, 2)->nullable();
            $table->decimal('tope_gasto_diario_cuenta', 12, 2)->nullable();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ads_config');
    }
};
