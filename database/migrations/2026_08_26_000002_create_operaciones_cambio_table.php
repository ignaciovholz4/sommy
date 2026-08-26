<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operaciones_cambio', function (Blueprint $table) {
            $table->id();

            $table->enum('tipo', ['compra', 'venta']);
            $table->foreignId('moneda_id')->constrained('monedas')->cascadeOnDelete();

            $table->foreignId('cuenta_ars_id')->constrained('cuentas')->cascadeOnDelete();
            $table->foreignId('cuenta_moneda_id')->constrained('cuentas')->cascadeOnDelete();

            $table->decimal('monto_moneda', 18, 2);
            $table->decimal('cotizacion', 14, 4);
            $table->decimal('monto_ars', 18, 2);

            $table->date('fecha');
            $table->text('observaciones')->nullable();

            $table->foreignId('movimiento_ars_id')->nullable()->constrained('movimientos')->nullOnDelete();
            $table->foreignId('movimiento_moneda_id')->nullable()->constrained('movimientos')->nullOnDelete();

            // Solo aplica a tipo=compra: cuanto de este lote no se consumio todavia (FIFO)
            $table->decimal('disponible', 18, 2)->nullable();
            // Solo aplica a tipo=venta: ganancia/perdida realizada (monto_ars - costo FIFO)
            $table->decimal('resultado', 18, 2)->nullable();

            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['moneda_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operaciones_cambio');
    }
};
