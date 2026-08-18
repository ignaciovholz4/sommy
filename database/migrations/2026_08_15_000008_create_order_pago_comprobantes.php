<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comprobantes de pago de pedidos: foto de la transferencia, captura de
 * MercadoPago, etc. Quedan registrados junto al pedido.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_pago_comprobantes')) {
            Schema::create('order_pago_comprobantes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->string('archivo', 255);
                $table->string('mime', 100)->nullable();
                $table->string('nota', 200)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_pago_comprobantes');
    }
};
