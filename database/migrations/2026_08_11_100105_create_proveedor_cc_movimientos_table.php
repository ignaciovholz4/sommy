<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proveedor_cc_movimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proveedor_id');
            // debe: aumenta lo que le debemos al proveedor | haber: lo reduce (pagos, NC)
            $table->enum('tipo', ['debe', 'haber']);
            $table->enum('origen', ['compra', 'pago', 'ajuste', 'nota_credito']);
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->unsignedBigInteger('movimiento_id')->nullable();
            $table->decimal('monto', 14, 2);
            $table->date('fecha_vencimiento')->nullable();
            // Estado de cancelación: solo tiene sentido en las filas "debe" (imputación FIFO)
            $table->enum('estado', ['pendiente', 'parcial', 'pagado'])->default('pendiente');
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('proveedor_id')->references('idproveedor')->on('proveedores')->cascadeOnDelete();
            $table->foreign('compra_id')->references('idcompra')->on('compras')->nullOnDelete();
            $table->foreign('movimiento_id')->references('id')->on('movimientos')->nullOnDelete();

            $table->index('proveedor_id');
            $table->index(['estado', 'fecha_vencimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor_cc_movimientos');
    }
};
