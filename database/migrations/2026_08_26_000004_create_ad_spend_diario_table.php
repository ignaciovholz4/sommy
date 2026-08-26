<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ad_spend_diario', function (Blueprint $table) {
            $table->id();
            $table->enum('plataforma', ['meta', 'google']);
            $table->date('fecha');
            $table->decimal('monto', 14, 2);
            $table->string('moneda', 10)->default('ARS');
            $table->timestamp('sincronizado_at')->nullable();
            $table->timestamps();

            $table->unique(['plataforma', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_spend_diario');
    }
};
