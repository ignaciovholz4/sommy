<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gasto_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            // Categoría padre opcional (permite armar árbol simple de 2 niveles o más)
            $table->unsignedBigInteger('padre_id')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('padre_id')->references('id')->on('gasto_categorias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gasto_categorias');
    }
};
