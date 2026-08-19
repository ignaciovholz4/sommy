<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * cliente_cc_movimientos no tenia nocion de vencimiento (a diferencia de
 * proveedor_cc_movimientos). Se agrega para poder detectar deuda vencida de
 * clientes y accionar la cobranza. Solo se completa hacia adelante: las filas
 * historicas quedan con fecha_vencimiento null ("vencimiento desconocido"),
 * nunca se asumen vencidas ni al dia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cliente_cc_movimientos', function (Blueprint $table) {
            $table->date('fecha_vencimiento')->nullable()->after('monto');
            $table->enum('estado', ['pendiente', 'parcial', 'pagado'])->default('pendiente')->after('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::table('cliente_cc_movimientos', function (Blueprint $table) {
            $table->dropColumn(['fecha_vencimiento', 'estado']);
        });
    }
};
