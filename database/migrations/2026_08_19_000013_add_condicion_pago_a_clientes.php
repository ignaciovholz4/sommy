<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plazo de pago del cliente (misma idea que proveedores.condicion_pago_dias):
 * define cuando un cargo en cuenta corriente pasa a estar "vencido".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unsignedInteger('condicion_pago_dias')->nullable()->after('dni_cuit');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('condicion_pago_dias');
        });
    }
};
