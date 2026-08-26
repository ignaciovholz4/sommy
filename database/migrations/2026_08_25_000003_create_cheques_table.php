<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();

            $table->enum('tipo', ['tercero', 'propio']);
            $table->string('numero')->nullable();
            $table->string('banco_emisor')->nullable();
            $table->string('contraparte_nombre')->nullable();
            $table->string('contraparte_cuit', 20)->nullable();

            $table->decimal('monto', 14, 2);
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_cobro');

            $table->enum('estado', ['en_cartera', 'depositado', 'acreditado', 'rechazado', 'entregado', 'anulado'])
                ->default('en_cartera');

            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas')->nullOnDelete();
            $table->foreignId('movimiento_id')->nullable()->constrained('movimientos')->nullOnDelete();
            $table->foreignId('movimiento_entrega_id')->nullable()->constrained('movimientos')->nullOnDelete();

            $table->nullableMorphs('origen');

            $table->string('adjunto_path')->nullable();
            $table->string('adjunto_nombre')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['tipo', 'estado']);
            $table->index('fecha_cobro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
