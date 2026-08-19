<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Umbral de stock por sucursal usado por la reposicion inteligente:
     * por debajo de este numero, el articulo entra en la sugerencia semanal.
     */
    public function up(): void
    {
        Schema::table('sucursal_articulo', function (Blueprint $table) {
            $table->unsignedInteger('stock_minimo')->nullable()->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('sucursal_articulo', function (Blueprint $table) {
            $table->dropColumn('stock_minimo');
        });
    }
};
