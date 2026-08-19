<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chat interno de reportes: cada usuario puede tener varias sesiones de
 * conversacion con el analista IA, cada una con su historial de mensajes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_chat_sesiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('titulo')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('reportes_chat_mensajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sesion_id');
            $table->enum('role', ['user', 'assistant', 'tool']);
            $table->text('content')->nullable();
            $table->json('tool_calls')->nullable();
            $table->string('tool_name')->nullable();
            $table->string('tool_call_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('sesion_id')->references('id')->on('reportes_chat_sesiones')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_chat_mensajes');
        Schema::dropIfExists('reportes_chat_sesiones');
    }
};
