<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedBigInteger('gasto_categoria_id');
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->string('descripcion');
            $table->decimal('monto', 14, 2);
            // Cuenta y movimiento se completan al registrar el pago
            $table->unsignedBigInteger('cuenta_id')->nullable();
            $table->unsignedBigInteger('movimiento_id')->nullable();
            $table->string('comprobante_path')->nullable();
            // Recurrencia (alquiler, servicios, etc.)
            $table->boolean('es_recurrente')->default(false);
            $table->enum('frecuencia', ['semanal', 'mensual', 'anual'])->nullable();
            $table->date('proximo_vencimiento')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');
            $table->timestamps();

            $table->foreign('gasto_categoria_id')->references('id')->on('gasto_categorias')->restrictOnDelete();
            $table->foreign('proveedor_id')->references('idproveedor')->on('proveedores')->nullOnDelete();
            $table->foreign('cuenta_id')->references('id')->on('cuentas')->nullOnDelete();
            $table->foreign('movimiento_id')->references('id')->on('movimientos')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->nullOnDelete();

            $table->index(['fecha', 'estado']);
            $table->index('gasto_categoria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
