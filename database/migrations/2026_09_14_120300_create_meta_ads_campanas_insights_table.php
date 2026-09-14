<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gasto diario por campana (a diferencia de ad_spend_diario, que es el total
 * agregado de la cuenta y ya lo usa el dashboard actual). Se usa para cruzar
 * contra order_ecommerce por utm_campaign y calcular ROAS/CAC por campana.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ads_campanas_insights', function (Blueprint $table) {
            $table->id();
            $table->string('meta_campaign_id');
            $table->string('nombre_campana')->nullable();
            $table->date('fecha');
            $table->decimal('spend', 12, 2)->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamp('sincronizado_at')->nullable();
            $table->timestamps();

            $table->unique(['meta_campaign_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ads_campanas_insights');
    }
};
