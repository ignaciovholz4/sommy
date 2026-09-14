<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ads_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // crear_campana | pausar | activar | subir_presupuesto | bajar_presupuesto
            $table->string('accion');
            $table->string('meta_campaign_id')->nullable();
            $table->string('meta_adset_id')->nullable();
            $table->string('nombre_campana')->nullable();
            $table->json('valor_anterior')->nullable();
            $table->json('valor_nuevo')->nullable();
            $table->boolean('exitoso')->default(true);
            $table->text('error')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['meta_campaign_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ads_auditoria');
    }
};
