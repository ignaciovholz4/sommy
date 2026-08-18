<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->id();
            // venta: envío de un pedido al cliente | compra: flete de mercadería del proveedor
            $table->enum('tipo', ['venta', 'compra']);
            // order_ecommerce.order_id es unsigned INT (no bigint)
            $table->unsignedInteger('order_ecommerce_id')->nullable();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->unsignedBigInteger('transportista_id');
            $table->decimal('costo', 14, 2)->default(0);
            $table->enum('pagado_por', ['cliente', 'empresa'])->default('cliente');
            $table->enum('estado', ['pendiente', 'despachado', 'en_transito', 'entregado', 'fallido'])->default('pendiente');
            $table->dateTime('fecha_despacho')->nullable();
            $table->date('fecha_entrega_estimada')->nullable();
            $table->dateTime('fecha_entrega_real')->nullable();
            $table->string('tracking', 120)->nullable();
            $table->string('direccion_entrega')->nullable();
            $table->text('notas')->nullable();
            // Gasto generado automáticamente cuando el flete lo paga la empresa
            $table->unsignedBigInteger('gasto_id')->nullable();
            $table->timestamps();

            $table->foreign('order_ecommerce_id')->references('order_id')->on('order_ecommerce')->nullOnDelete();
            $table->foreign('compra_id')->references('idcompra')->on('compras')->nullOnDelete();
            $table->foreign('transportista_id')->references('id')->on('transportistas')->restrictOnDelete();
            $table->foreign('gasto_id')->references('id')->on('gastos')->nullOnDelete();

            $table->index(['estado', 'transportista_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envios');
    }
};
