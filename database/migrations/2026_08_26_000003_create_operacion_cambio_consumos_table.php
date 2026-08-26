<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operacion_cambio_consumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('operaciones_cambio')->cascadeOnDelete();
            $table->foreignId('compra_id')->constrained('operaciones_cambio')->cascadeOnDelete();
            $table->decimal('cantidad', 18, 2);
            $table->decimal('costo_ars', 18, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operacion_cambio_consumos');
    }
};
