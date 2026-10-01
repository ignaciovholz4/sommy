<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Galería propia de cada combo. Un combo "vive" sobre un producto ancla
 * (productos.combo_descuento_pct > 0), pero su foto de vidriera no tiene por
 * qué ser la del producto suelto: el combo se muestra armado (colchón + base
 * + almohadas). Estas imágenes son independientes de producto_imagenes para
 * no ensuciar la galería de la ficha del producto ni el Creative Studio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo_imagenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id'); // ancla del combo
            $table->string('path', 255);               // relativo a public/
            $table->unsignedTinyInteger('orden')->default(0);
            $table->string('alt', 255)->nullable();
            $table->timestamps();

            $table->foreign('producto_id')->references('idarticulo')->on('productos')->onDelete('cascade');
            $table->index(['producto_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_imagenes');
    }
};
