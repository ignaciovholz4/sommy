<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Precio mayorista: un precio aparte que NUNCA se muestra en el ecommerce
 * (eso sigue siendo pventa_con_iva/pventa_variante, el "minorista"), solo se
 * usa para el catálogo impreso/exportado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('pventa_mayorista', 10, 2)->nullable()->after('pventa_con_iva');
        });

        Schema::table('producto_combinaciones', function (Blueprint $table) {
            $table->decimal('pventa_mayorista', 10, 2)->nullable()->after('pventa_variante');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('pventa_mayorista');
        });

        Schema::table('producto_combinaciones', function (Blueprint $table) {
            $table->dropColumn('pventa_mayorista');
        });
    }
};
