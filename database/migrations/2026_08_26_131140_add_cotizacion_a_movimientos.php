<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un pago puede salir de una cuenta en moneda extranjera (ej. Caja2 dólares).
 * `total` sigue siendo el monto en la moneda propia de la cuenta (así el saldo
 * de esa caja/banco no se rompe). `total_ars` es el equivalente en pesos —lo
 * que realmente cubre de una venta/compra (siempre en ARS)— usando `cotizacion`
 * cuando la cuenta no es ARS. Para movimientos ya existentes total_ars = total
 * (eran todos en ARS).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->decimal('cotizacion', 18, 4)->nullable()->after('total');
            $table->decimal('total_ars', 18, 2)->nullable()->after('cotizacion');
        });

        DB::statement('UPDATE movimientos SET total_ars = total WHERE total_ars IS NULL');
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropColumn(['cotizacion', 'total_ars']);
        });
    }
};
