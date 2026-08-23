<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Una devolucion de venta puede resolverse como reintegro total (como hasta
 * ahora) o como cambio por otro producto, con diferencia de precio a favor
 * del cliente o del negocio calculada automaticamente.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('devoluciones', function (Blueprint $table) {
            $table->enum('resolucion', ['reintegro', 'cambio'])->default('reintegro')->after('tipo');
            $table->string('producto_anterior')->nullable()->after('motivo');
            $table->string('producto_nuevo')->nullable()->after('producto_anterior');
            $table->decimal('diferencia', 12, 2)->default(0)->after('monto');
            $table->unsignedBigInteger('user_id')->nullable()->after('diferencia');
        });
    }

    public function down(): void
    {
        Schema::table('devoluciones', function (Blueprint $table) {
            $table->dropColumn(['resolucion', 'producto_anterior', 'producto_nuevo', 'diferencia', 'user_id']);
        });
    }
};
