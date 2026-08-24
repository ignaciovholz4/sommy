<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimientos_bancarios_importados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuentas')->cascadeOnDelete();

            $table->string('archivo_nombre')->nullable();
            $table->string('archivo_hash', 64)->nullable(); // detecta si ya se importó el mismo archivo

            $table->date('fecha');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 18, 2);
            $table->string('descripcion')->nullable();
            $table->string('referencia')->nullable();
            $table->json('fila_original')->nullable(); // fila cruda del archivo, para soporte/debug

            $table->enum('estado', ['pendiente', 'conciliado', 'descartado'])->default('pendiente');
            $table->foreignId('movimiento_id')->nullable()->constrained('movimientos')->nullOnDelete();
            $table->foreignId('conciliado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('conciliado_at')->nullable();

            $table->timestamps();

            $table->index(['cuenta_id', 'estado']);
            // Evita importar dos veces la misma fila del mismo extracto para la misma cuenta
            $table->unique(['cuenta_id', 'fecha', 'monto', 'tipo', 'referencia', 'descripcion'], 'mov_banc_imp_dedupe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_bancarios_importados');
    }
};
