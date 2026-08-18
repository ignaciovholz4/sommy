<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('moneda_id')->constrained('monedas');
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['sucursal_id', 'nombre']);
            $table->index(['moneda_id', 'activa']);
        });
    }
    public function down(): void { Schema::dropIfExists('cajas'); }
};