<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inversores: quién puso plata en el negocio, su % de participación (si
 * corresponde) y el historial de aportes/retiros/reparto de ganancias.
 * Cuando un movimiento sale/entra de una caja o banco real, queda también
 * como Movimiento (mismo patrón que gastos/cheques/CxP) para que el saldo
 * de esa cuenta y el resumen de caja lo reflejen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inversores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('porcentaje_participacion', 5, 2)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('inversor_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inversor_id')->constrained('inversores')->cascadeOnDelete();
            $table->enum('tipo', ['aporte', 'retiro', 'distribucion']);
            $table->decimal('monto', 18, 2);
            $table->string('concepto', 250)->nullable();
            $table->date('fecha');
            $table->foreignId('cuenta_id')->nullable()->constrained('cuentas')->nullOnDelete();
            $table->unsignedBigInteger('movimiento_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['inversor_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inversor_movimientos');
        Schema::dropIfExists('inversores');
    }
};
