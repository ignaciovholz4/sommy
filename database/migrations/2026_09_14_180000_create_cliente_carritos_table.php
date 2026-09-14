<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Snapshot del carrito (localStorage) de cada cliente logueado, para poder
        // detectar abandono y avisarle por mail — nunca se conoce el carrito de un
        // visitante anónimo (no hay a quién mandarle nada).
        Schema::create('cliente_carritos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->unique();
            $table->json('items');
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('aviso_enviado_at')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('idcliente')->on('clientes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_carritos');
    }
};
