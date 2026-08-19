<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Medios de pago en movimientos: además de efectivo/bancos/tarjetas se
 * registra el medio elegido (transferencia, tarjeta, cheque, etc.) y los
 * cheques van a su propia columna (son papeles hasta que se depositan,
 * no deben mezclarse con el efectivo del cierre de caja).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            if (!Schema::hasColumn('movimientos', 'medio')) {
                $table->string('medio', 30)->nullable()->after('tipo');
            }
            if (!Schema::hasColumn('movimientos', 'cheques')) {
                $table->decimal('cheques', 14, 2)->default(0)->after('tarjetas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            if (Schema::hasColumn('movimientos', 'medio')) {
                $table->dropColumn('medio');
            }
            if (Schema::hasColumn('movimientos', 'cheques')) {
                $table->dropColumn('cheques');
            }
        });
    }
};
