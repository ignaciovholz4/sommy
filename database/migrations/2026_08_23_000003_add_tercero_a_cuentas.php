<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cuentas de terceros: alias/CUIT de una cuenta que no es del negocio (de un
 * familiar/socio) usada para cobrar ventas y pedidos. Se modela como un tipo
 * mas de `cuentas` (no una tabla aparte) para reusar el calculo de saldo y
 * el listado de movimientos que ya funcionan para bancos.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('cuentas', function (Blueprint $table) {
            $table->string('alias', 60)->nullable()->after('tipo');
            $table->string('cuit', 20)->nullable()->after('alias');
        });

        DB::statement("ALTER TABLE cuentas MODIFY tipo ENUM('caja','banco','tercero') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cuentas MODIFY tipo ENUM('caja','banco') NOT NULL");

        Schema::table('cuentas', function (Blueprint $table) {
            $table->dropColumn(['alias', 'cuit']);
        });
    }
};
