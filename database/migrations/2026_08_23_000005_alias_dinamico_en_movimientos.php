<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rediseño de cuentas de terceros: el alias/CUIT deja de ser un dato fijo de
 * la cuenta y pasa a registrarse en cada movimiento. Así una única cuenta
 * "Terceros" concentra la plata que está en manos de otros, y el panel de
 * control agrupa por alias/CUIT para saber cuánto fue a cada uno.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            if (!Schema::hasColumn('movimientos', 'alias_tercero')) {
                $table->string('alias_tercero', 60)->nullable()->after('medio')->index();
            }
            if (!Schema::hasColumn('movimientos', 'cuit_tercero')) {
                $table->string('cuit_tercero', 20)->nullable()->after('alias_tercero')->index();
            }
        });

        // El alias fijo por cuenta se descarta: era el diseño anterior
        Schema::table('cuentas', function (Blueprint $table) {
            if (Schema::hasColumn('cuentas', 'alias')) {
                $table->dropColumn('alias');
            }
            if (Schema::hasColumn('cuentas', 'cuit')) {
                $table->dropColumn('cuit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropColumn(['alias_tercero', 'cuit_tercero']);
        });

        Schema::table('cuentas', function (Blueprint $table) {
            $table->string('alias', 60)->nullable()->after('tipo');
            $table->string('cuit', 20)->nullable()->after('alias');
        });
    }
};
