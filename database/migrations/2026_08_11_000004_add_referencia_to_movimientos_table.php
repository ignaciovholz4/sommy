<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            if (!Schema::hasColumn('movimientos', 'referencia_type')) {
                // Vinculo polimorfico opcional para fuentes nuevas (gastos, pagos CxP, fletes).
                // Las ventas/compras existentes siguen usando el campo string `comprobante`.
                $table->string('referencia_type')->nullable();
                $table->unsignedBigInteger('referencia_id')->nullable();
                $table->index(['referencia_type', 'referencia_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            if (Schema::hasColumn('movimientos', 'referencia_type')) {
                $table->dropIndex(['referencia_type', 'referencia_id']);
                $table->dropColumn(['referencia_type', 'referencia_id']);
            }
        });
    }
};
