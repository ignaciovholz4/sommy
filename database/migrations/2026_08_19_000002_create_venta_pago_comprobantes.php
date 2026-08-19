<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Comprobantes de pago de ventas manuales (foto de transferencia, recibo,
 * captura): quedan asociados a la venta, espejo de order_pago_comprobantes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('venta_pago_comprobantes')) {
            Schema::create('venta_pago_comprobantes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('venta_id')->index();
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
        Schema::dropIfExists('venta_pago_comprobantes');
    }
};
