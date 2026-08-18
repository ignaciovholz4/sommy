<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ventas manuales con revendedor: quien vendió queda registrado en la venta
 * y genera su comisión en revendedor_comisiones (que hasta ahora solo
 * referenciaba pedidos del ecommerce via order_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ventas', 'revendedor_id')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->unsignedBigInteger('revendedor_id')->nullable()->after('cliente_id')->index();
            });
        }

        if (!Schema::hasColumn('revendedor_comisiones', 'venta_id')) {
            Schema::table('revendedor_comisiones', function (Blueprint $table) {
                $table->unsignedBigInteger('venta_id')->nullable()->after('order_id')->index();
            });
        }

        // Una comisión ahora nace de un pedido ecommerce O de una venta manual
        DB::statement('ALTER TABLE revendedor_comisiones MODIFY order_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (Schema::hasColumn('revendedor_comisiones', 'venta_id')) {
            Schema::table('revendedor_comisiones', function (Blueprint $table) {
                $table->dropColumn('venta_id');
            });
        }
        if (Schema::hasColumn('ventas', 'revendedor_id')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->dropColumn('revendedor_id');
            });
        }
    }
};
