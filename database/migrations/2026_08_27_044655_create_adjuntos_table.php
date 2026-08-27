<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adjuntos genéricos: cualquier archivo (remito, comprobante, foto, etc.)
 * pegado a un registro de cualquier módulo (compra, venta, presupuesto,
 * devolución, envío, pedido de compra). No hay borrado desde la UI: los
 * archivos que se suben quedan, a propósito (pedido explícito del dueño).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjuntos', function (Blueprint $table) {
            $table->id();
            $table->string('adjuntable_type');
            $table->unsignedBigInteger('adjuntable_id');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['adjuntable_type', 'adjuntable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjuntos');
    }
};
