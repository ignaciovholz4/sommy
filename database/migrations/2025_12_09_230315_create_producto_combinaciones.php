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
        Schema::create('producto_combinaciones', function (Blueprint $table) {
            $table->bigIncrements('idcombinacion');
            $table->unsignedBigInteger('producto_id');
            $table->string('combinacion');
            $table->json('json_detalle');
            $table->timestamps();

            $table->foreign('producto_id')->references('idarticulo')->on('productos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_combinaciones');
    }
};
