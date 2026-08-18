<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('caja_aperturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->cascadeOnDelete();
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('fondo_inicial', 18, 2); // Fondo de Caja
            $table->boolean('abierta')->default(true); // estado: abierta/cerrada
            $table->timestamps();

            $table->index(['caja_id', 'abierta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_aperturas');
    }
};