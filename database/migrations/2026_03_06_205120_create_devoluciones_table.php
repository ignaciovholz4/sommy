<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            // Tipo de devolución: venta o compra
            $table->enum('tipo', ['venta', 'compra']);
            // Referencia a la venta o compra
            $table->unsignedBigInteger('referencia_id');
            // Motivo de la devolución
            $table->string('motivo')->nullable();
            // Fecha de la devolución
            $table->dateTime('fecha');
            // Monto total de la devolución
            $table->decimal('monto', 12, 2);
            $table->timestamps();
            // Índices para búsquedas rápidas
            $table->index(['tipo', 'referencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};