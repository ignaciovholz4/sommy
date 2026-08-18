<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movimientos_stock', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sucursal_origen_id')
                ->nullable()
                ->constrained('sucursales')
                ->onDelete('set null');

            $table->foreignId('sucursal_destino_id')
                ->nullable()
                ->constrained('sucursales')
                ->onDelete('set null');

            // ✅ Corregido: productos usa idarticulo
            $table->unsignedBigInteger('articulo_id');
            $table->foreign('articulo_id')
                ->references('idarticulo')
                ->on('productos')
                ->onDelete('cascade');

            $table->integer('cantidad');
            $table->enum('tipo', ['entrada', 'salida', 'transferencia', 'ajuste']);
            $table->string('observacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_stock');
    }
};