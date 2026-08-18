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
        Schema::create('producto_atributo_variantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('atributo_id');
            $table->string('valor');
            $table->timestamps();

            $table->foreign('atributo_id')->references('id')->on('producto_atributos')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_atributo_variantes');
    }
};
