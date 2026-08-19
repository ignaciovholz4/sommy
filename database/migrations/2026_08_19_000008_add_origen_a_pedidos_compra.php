<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distingue los pedidos de compra armados a mano de los que genera
     * automaticamente la reposicion inteligente, para mostrarlo en el listado.
     */
    public function up(): void
    {
        Schema::table('pedidos_compra', function (Blueprint $table) {
            $table->enum('origen', ['manual', 'ia_reposicion'])->default('manual')->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_compra', function (Blueprint $table) {
            $table->dropColumn('origen');
        });
    }
};
