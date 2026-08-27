<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_relacionados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idarticulo');
            $table->unsignedBigInteger('relacionado_id');
            $table->timestamps();

            $table->foreign('idarticulo')->references('idarticulo')->on('productos')->onDelete('cascade');
            $table->foreign('relacionado_id')->references('idarticulo')->on('productos')->onDelete('cascade');
            $table->unique(['idarticulo', 'relacionado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_relacionados');
    }
};
