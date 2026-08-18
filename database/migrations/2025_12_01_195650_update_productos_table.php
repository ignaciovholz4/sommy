<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Eliminamos columnas viejas
            $table->dropColumn(['stock', 'marca', 'iva', 'pcompra', 'pventa']);

            // Agregamos nuevas columnas de IVA y precios
            $table->unsignedInteger('iva_compra_id')->nullable();
            $table->decimal('pcompra_sin_iva', 10, 2)->default(0);
            $table->decimal('pcompra_con_iva', 10, 2)->default(0);

            $table->unsignedInteger('iva_venta_id')->nullable();
            $table->decimal('pventa_sin_iva', 10, 2)->default(0);
            $table->decimal('pventa_con_iva', 10, 2)->default(0);

            // 🔗 Nuevas columnas de relaciones
            $table->unsignedInteger('marca_id')->nullable();
            $table->unsignedInteger('unidad_id')->nullable();
            $table->boolean('articulo_pesable_balanza')->default(false);

            // Claves foráneas
            $table->foreign('iva_compra_id')->references('idiva')->on('iva');
            $table->foreign('iva_venta_id')->references('idiva')->on('iva');
            $table->foreign('marca_id')->references('idmarca')->on('marcas');
            $table->foreign('unidad_id')->references('idunidad')->on('unidades');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Revertimos cambios
            $table->dropForeign(['iva_compra_id']);
            $table->dropForeign(['iva_venta_id']);
            $table->dropColumn([
                'iva_compra_id',
                'pcompra_sin_iva',
                'pcompra_con_iva',
                'iva_venta_id',
                'pventa_sin_iva',
                'pventa_con_iva',
            ]);

            // Restauramos columnas viejas
            $table->integer('stock')->default(0);
            $table->string('marca');
            $table->decimal('iva', 5, 2)->default(0);
            $table->decimal('pcompra', 10, 2)->default(0);
            $table->decimal('pventa', 10, 2)->default(0);
        });
    }
};