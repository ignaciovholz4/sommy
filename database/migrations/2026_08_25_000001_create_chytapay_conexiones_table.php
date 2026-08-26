<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chytapay_conexiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->unique()->constrained('cuentas')->cascadeOnDelete();

            $table->text('id_token');
            $table->text('refresh_token');
            $table->timestamp('token_expires_at')->nullable();

            $table->string('comercio_nombre')->nullable();
            $table->string('comercio_email')->nullable();

            $table->foreignId('conectado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('conectado_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chytapay_conexiones');
    }
};
