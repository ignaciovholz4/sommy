<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Direccional (no simétrica como producto_relacionados): comprar
        // "idarticulo" habilita elegir UNO de sus "regalo_id" gratis.
        // No implica lo inverso.
        Schema::create('producto_regalos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idarticulo');
            $table->unsignedBigInteger('regalo_id');
            $table->timestamps();

            $table->unique(['idarticulo', 'regalo_id']);
            $table->foreign('idarticulo')->references('idarticulo')->on('productos')->onDelete('cascade');
            $table->foreign('regalo_id')->references('idarticulo')->on('productos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_regalos');
    }
};
