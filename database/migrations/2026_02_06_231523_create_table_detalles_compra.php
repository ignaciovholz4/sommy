<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->bigIncrements('id_detalle');

            $table->unsignedBigInteger('compra_id');
            $table->unsignedBigInteger('articulo_id');

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('iva', 5, 2); // porcentaje
            $table->decimal('subtotal_neto', 12, 2);
            $table->decimal('subtotal_con_iva', 12, 2);

            $table->timestamps();

            // Foreign keys
            $table->foreign('compra_id')->references('idcompra')->on('compras')->onDelete('cascade');
            $table->foreign('articulo_id')->references('idarticulo')->on('productos')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_compras');
    }
};