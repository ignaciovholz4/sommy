<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            $table->unsignedBigInteger('zona_envio_id')->nullable()->after('additional_info');
            $table->decimal('costo_envio', 12, 2)->default(0)->after('zona_envio_id');
            $table->decimal('descuento_pago', 12, 2)->default(0)->after('costo_envio');

            // Snapshot de la dirección de entrega del pedido (independiente del cliente)
            $table->string('direccion_localidad', 100)->nullable()->after('descuento_pago');
            $table->string('direccion_provincia', 60)->nullable()->after('direccion_localidad');
            $table->string('direccion_cp', 10)->nullable()->after('direccion_provincia');

            $table->foreign('zona_envio_id')->references('id')->on('zonas_envio')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            $table->dropForeign(['zona_envio_id']);
            $table->dropColumn([
                'zona_envio_id', 'costo_envio', 'descuento_pago',
                'direccion_localidad', 'direccion_provincia', 'direccion_cp',
            ]);
        });
    }
};
