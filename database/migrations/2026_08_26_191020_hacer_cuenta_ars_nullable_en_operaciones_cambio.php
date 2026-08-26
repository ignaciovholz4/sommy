<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Permite que una operación de cambio no tenga cuenta ARS: cuando un pago
 * de compra o un cobro de venta se hace en moneda extranjera, los pesos no
 * entran/salen de una cuenta real (van a saldar la deuda de esa compra/venta),
 * así que no hay una cuenta ARS que asociarle. referencia_type/id apuntan a
 * la Compra o Venta que generó la operación (mismo patrón que movimientos).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sin doctrine/dbal en este proyecto: MODIFY directo por SQL crudo.
        DB::statement('ALTER TABLE operaciones_cambio MODIFY cuenta_ars_id BIGINT UNSIGNED NULL');

        Schema::table('operaciones_cambio', function (Blueprint $table) {
            $table->string('referencia_type')->nullable()->after('creado_por');
            $table->unsignedBigInteger('referencia_id')->nullable()->after('referencia_type');
        });
    }

    public function down(): void
    {
        Schema::table('operaciones_cambio', function (Blueprint $table) {
            $table->dropColumn(['referencia_type', 'referencia_id']);
        });

        DB::statement('ALTER TABLE operaciones_cambio MODIFY cuenta_ars_id BIGINT UNSIGNED NOT NULL');
    }
};
