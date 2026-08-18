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
        Schema::create('puntos_venta', function (Blueprint $table) { 
            $table->id(); 
            $table->foreignId('sucursal_id')->constrained('sucursales')->onDelete('cascade'); 
            $table->string('nombre'); 
            $table->string('codigo')->unique()->nullable(); // ej: CAJA1, PV-ONLINE 
            $table->boolean('activo')->default(true); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_venta');
    }
};
