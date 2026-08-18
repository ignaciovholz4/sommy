<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Archivo adjunto del movimiento (comprobante de transferencia,
     * recibo, o cualquier archivo relacionado al pago).
     */
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->string('adjunto_path')->nullable()->after('observaciones');
            $table->string('adjunto_nombre')->nullable()->after('adjunto_path');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropColumn(['adjunto_path', 'adjunto_nombre']);
        });
    }
};
