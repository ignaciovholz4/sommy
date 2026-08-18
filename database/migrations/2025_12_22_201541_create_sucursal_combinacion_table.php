<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursal_combinacion', function (Blueprint $table) {
            $table->id(); // PK autoincremental
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('combinacion_id');
            $table->integer('stock')->default(0);
            $table->string('ubicacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // 🔗 Relaciones
            $table->foreign('sucursal_id')
                  ->references('id')->on('sucursales')
                  ->onDelete('cascade');

            $table->foreign('combinacion_id')
                  ->references('idcombinacion')->on('producto_combinaciones')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursal_combinacion');
    }
};