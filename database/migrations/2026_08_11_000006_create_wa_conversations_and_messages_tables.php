<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wa_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_account_id')->constrained('wa_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('phone_e164', 20);           // formato Meta: 549XXXXXXXXXX
            $table->string('profile_name')->nullable();
            $table->enum('status', ['nueva', 'en_atencion', 'esperando_cliente', 'cerrada'])->default('nueva');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ai_agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
            $table->enum('mode', ['bot', 'humano'])->default('bot');
            $table->dateTime('last_inbound_at')->nullable();   // para la ventana de 24 h
            $table->dateTime('last_message_at')->nullable()->index();
            $table->string('last_message_preview')->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            $table->timestamps();

            $table->unique(['wa_account_id', 'phone_e164']);
            $table->foreign('cliente_id')->references('idcliente')->on('clientes')->nullOnDelete();
        });

        Schema::create('wa_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('wa_conversations')->cascadeOnDelete();
            $table->string('wa_message_id')->nullable()->unique(); // id de Meta, idempotencia
            $table->enum('direction', ['in', 'out']);
            $table->string('type', 20)->default('text'); // text|image|audio|video|document|sticker|location|template|interactive
            $table->text('body')->nullable();
            $table->string('media_id')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_mime', 100)->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->nullable(); // solo salientes
            $table->string('error_code', 20)->nullable();
            $table->text('error_detail')->nullable();
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by_agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
            $table->boolean('is_internal_note')->default(false);
            $table->json('payload')->nullable();         // webhook crudo, por si hay que reprocesar
            $table->dateTime('wa_timestamp')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
        Schema::dropIfExists('wa_conversations');
    }
};
