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
        Schema::create('sucursal_articulo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->onDelete('cascade');

            // ✅ Corregido: productos usa idarticulo
            $table->unsignedBigInteger('articulo_id');
            $table->foreign('articulo_id')
                ->references('idarticulo')
                ->on('productos')
                ->onDelete('cascade');

            $table->integer('stock')->default(0);
            $table->string('ubicacion')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique(['sucursal_id', 'articulo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursal_articulo');
    }
};
