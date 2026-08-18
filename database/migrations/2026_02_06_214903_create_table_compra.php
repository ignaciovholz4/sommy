<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->bigIncrements('idcompra');

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('tipo_comprobante_id')->nullable();

            $table->string('num_folio', 20)->nullable()->unique();
            $table->date('fecha');
            $table->enum('estado', ['pendiente', 'aprobado', 'facturado'])->default('pendiente');

            $table->decimal('total_neto', 12, 2)->default(0);
            $table->decimal('total_con_iva', 12, 2)->default(0);

            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('proveedor_id')->references('idproveedor')->on('proveedores')->onDelete('cascade');
            $table->foreign('tipo_comprobante_id')->references('idtipo_comprobante')->on('tipos_comprobantes')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras');
    }
};