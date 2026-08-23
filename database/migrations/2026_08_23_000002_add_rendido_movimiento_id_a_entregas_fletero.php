<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vincula cada entrega ya rendida con el movimiento de caja/banco que
 * registro esa plata, para completar el flujo que la migracion original
 * de entregas_fletero dejo previsto pero sin implementar (columna `rendido`
 * que nadie actualizaba).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('entregas_fletero', function (Blueprint $table) {
            $table->foreignId('rendido_movimiento_id')->nullable()->after('rendido')
                ->constrained('movimientos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('entregas_fletero', function (Blueprint $table) {
            $table->dropForeign(['rendido_movimiento_id']);
            $table->dropColumn('rendido_movimiento_id');
        });
    }
};
