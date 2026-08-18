<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('monedas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 3)->unique();   // ARS, USD
            $table->string('nombre');                // Pesos Argentinos, Dólares
            $table->string('simbolo', 5)->nullable();// $, AR$
            $table->unsignedTinyInteger('decimales')->default(2);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('monedas'); }
};