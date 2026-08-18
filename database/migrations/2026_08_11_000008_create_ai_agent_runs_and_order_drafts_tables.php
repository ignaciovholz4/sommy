<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_agent_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_agent_id')->constrained('ai_agents')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('wa_conversations')->cascadeOnDelete();
            $table->unsignedBigInteger('wa_message_id_in')->nullable(); // wa_messages.id que disparo el run
            $table->unsignedTinyInteger('iterations')->default(0);
            $table->json('tool_calls')->nullable();
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->decimal('costo_estimado', 10, 4)->default(0); // USD
            $table->enum('status', ['ok', 'error', 'escalated'])->default('ok');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['ai_agent_id', 'created_at']);
        });

        Schema::create('wa_order_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('wa_conversations')->cascadeOnDelete();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->foreignId('ai_agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
            $table->json('items'); // [{producto_id, combinacion_id?, cantidad, precio_unitario, descripcion}]
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('costo_envio', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->json('datos_entrega')->nullable(); // {direccion, localidad, provincia, cp, zona_envio_id?}
            $table->text('notas')->nullable();
            $table->enum('status', ['borrador', 'pendiente_confirmacion', 'confirmado', 'rechazado', 'expirado'])->default('borrador');
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('order_ecommerce_id')->nullable(); // pedido real creado al confirmar (order_ecommerce usa increments)
            $table->timestamps();

            $table->foreign('cliente_id')->references('idcliente')->on('clientes')->nullOnDelete();
            $table->foreign('order_ecommerce_id')->references('order_id')->on('order_ecommerce')->nullOnDelete();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_order_drafts');
        Schema::dropIfExists('ai_agent_runs');
    }
};
