<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('producto_combinaciones', function (Blueprint $table) {
            $table->string('sku', 32)->nullable()->unique()->after('combinacion');
        });

        // Backfill: SKU autogenerado con prefijo V (namespace disjunto de productos.codigo, 13 dígitos)
        DB::statement("UPDATE producto_combinaciones SET sku = CONCAT('V', LPAD(idcombinacion, 12, '0')) WHERE sku IS NULL");
    }

    public function down()
    {
        Schema::table('producto_combinaciones', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};
