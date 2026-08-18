<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->cascadeOnDelete();
            $table->foreignId('caja_apertura_id')->nullable()->constrained('caja_aperturas')->nullOnDelete();

            $table->dateTime('fecha');                 // Fecha del movimiento
            $table->enum('tipo', ['ingreso','egreso']); // Movimiento: ingreso/egreso

            $table->string('cliente_proveedor')->nullable(); // Texto plano
            $table->string('comprobante')->nullable();       // Texto plano
            $table->text('observaciones')->nullable();

            $table->decimal('efectivo', 18, 2)->default(0);
            $table->decimal('bancos', 18, 2)->default(0);
            $table->decimal('tarjetas', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);

            $table->timestamps();

            $table->index(['caja_id', 'caja_apertura_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};