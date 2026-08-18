<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pedidos de compra (órdenes de compra borrador): NO afectan stock, caja ni CxP.
     * Al convertirse en compra se crea la Compra real y el pedido queda trazado vía compra_id.
     */
    public function up(): void
    {
        Schema::create('pedidos_compra', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('proveedor_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('tipo_comprobante_id')->nullable();
            $table->string('num_folio', 20)->nullable()->unique();
            $table->date('fecha');
            $table->enum('estado', ['borrador', 'convertido', 'anulado'])->default('borrador');
            $table->boolean('a_credito')->default(false);
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->decimal('total_neto', 12, 2)->default(0);
            $table->decimal('total_con_iva', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('proveedor_id')->references('idproveedor')->on('proveedores');
            $table->foreign('sucursal_id')->references('id')->on('sucursales');
            $table->foreign('tipo_comprobante_id')->references('idtipo_comprobante')->on('tipos_comprobantes');
            $table->foreign('compra_id')->references('idcompra')->on('compras')->nullOnDelete();
        });

        Schema::create('detalle_pedidos_compra', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pedido_compra_id');
            $table->unsignedBigInteger('articulo_id');
            $table->unsignedBigInteger('combinacion_id')->nullable();
            $table->unsignedBigInteger('tipo_producto_id');
            $table->unsignedBigInteger('price_list_id')->nullable();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento', 5, 2)->default(0);
            $table->decimal('iva', 5, 2);
            $table->decimal('subtotal_neto', 12, 2);
            $table->decimal('subtotal_con_iva', 12, 2);
            $table->timestamps();

            $table->foreign('pedido_compra_id')->references('id')->on('pedidos_compra')->cascadeOnDelete();
            $table->foreign('articulo_id')->references('idarticulo')->on('productos')->restrictOnDelete();
            $table->foreign('combinacion_id')->references('idcombinacion')->on('producto_combinaciones')->nullOnDelete();
            $table->foreign('price_list_id')->references('id')->on('price_lists')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos_compra');
        Schema::dropIfExists('pedidos_compra');
    }
};
