<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Los envíos también pueden nacer de una venta manual del mostrador
 * (además de pedidos ecommerce y compras a proveedores).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE envios MODIFY tipo ENUM('venta','compra','venta_manual') NOT NULL");

        if (!Schema::hasColumn('envios', 'venta_id')) {
            Schema::table('envios', function (Blueprint $table) {
                $table->unsignedBigInteger('venta_id')->nullable()->after('compra_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('envios', 'venta_id')) {
            Schema::table('envios', function (Blueprint $table) {
                $table->dropColumn('venta_id');
            });
        }
        DB::statement("ALTER TABLE envios MODIFY tipo ENUM('venta','compra') NOT NULL");
    }
};
