<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Clasificación comercial de la venta: minorista (default) o mayorista. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ventas', 'tipo_venta')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->enum('tipo_venta', ['minorista', 'mayorista'])->default('minorista')->after('tipo_comprobante_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ventas', 'tipo_venta')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->dropColumn('tipo_venta');
            });
        }
    }
};
