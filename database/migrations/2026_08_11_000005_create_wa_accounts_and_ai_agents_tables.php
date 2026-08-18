<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wa_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('phone_number_id')->unique();
            $table->string('waba_id')->nullable();
            $table->string('display_phone', 30)->nullable();
            $table->text('token')->nullable();          // encrypted cast en el modelo
            $table->string('verify_token')->nullable();
            $table->text('app_secret')->nullable();     // encrypted cast en el modelo
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('activo')->default(false);
            $table->enum('provider', ['openai', 'anthropic'])->default('openai');
            $table->string('model')->default('gpt-4o-mini');
            $table->text('system_prompt');
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->json('tools_enabled')->nullable();  // ["buscar_productos","consultar_stock","cotizar","crear_pedido","derivar_a_humano"]
            $table->json('horario')->nullable();        // {"dias":[1..7],"desde":"09:00","hasta":"18:00"}
            $table->boolean('solo_fuera_de_horario')->default(false);
            $table->unsignedSmallInteger('max_turnos_sin_humano')->default(10);
            $table->text('mensaje_derivacion')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable(); // sucursal de referencia para stock
            $table->decimal('tope_costo_diario', 10, 2)->nullable(); // USD; null = sin tope
            $table->timestamps();

            $table->foreign('sucursal_id')->references('id')->on('sucursales')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agents');
        Schema::dropIfExists('wa_accounts');
    }
};
