<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adjuntos de compras (remitos, facturas, fotos de comprobantes).
     * Se cargan al convertir un pedido en compra o desde la compra misma.
     */
    public function up(): void
    {
        Schema::create('compra_adjuntos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('compra_id');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('compra_id')->references('idcompra')->on('compras')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_adjuntos');
    }
};
