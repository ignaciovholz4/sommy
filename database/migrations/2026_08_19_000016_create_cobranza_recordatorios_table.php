<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cola de aprobacion de recordatorios de cobranza: el agente IA arma el
 * borrador (tier + plantilla + nota interna) y un humano lo aprueba y envia.
 * Nunca se envia solo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobranza_recordatorios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->decimal('monto_vencido', 14, 2);
            $table->unsignedInteger('dias_vencido');
            $table->enum('tier', ['suave', 'firme', 'urgente']);
            $table->unsignedBigInteger('wa_template_id')->nullable();
            $table->json('template_params')->nullable();
            $table->text('nota_interna')->nullable();
            $table->enum('estado', ['pendiente_revision', 'aprobado', 'enviado', 'descartado', 'fallido'])->default('pendiente_revision');
            $table->unsignedBigInteger('wa_conversation_id')->nullable();
            $table->unsignedBigInteger('wa_message_id')->nullable();
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('idcliente')->on('clientes')->cascadeOnDelete();
            $table->foreign('wa_template_id')->references('id')->on('wa_templates')->nullOnDelete();
            $table->foreign('wa_conversation_id')->references('id')->on('wa_conversations')->nullOnDelete();
            $table->foreign('wa_message_id')->references('id')->on('wa_messages')->nullOnDelete();
            $table->foreign('revisado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobranza_recordatorios');
    }
};
