<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id('id_detalle');

            $table->foreignId('venta_id')->constrained('ventas', 'idventa')->onDelete('cascade');
            $table->foreignId('articulo_id')->constrained('productos', 'idarticulo')->onDelete('cascade');

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('iva', 5, 2)->default(0); // porcentaje
            $table->decimal('subtotal_neto', 12, 2);
            $table->decimal('subtotal_con_iva', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};