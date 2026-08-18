<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('producto_combinaciones', function (Blueprint $table) {
            $table->decimal('pcompra_variante', 10, 2)->nullable()->after('json_detalle');
            $table->decimal('pventa_variante', 10, 2)->nullable()->after('pcompra_variante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto_combinaciones', function (Blueprint $table) {
            $table->dropColumn(['pcompra_variante', 'pventa_variante']);
        });
    }
};
