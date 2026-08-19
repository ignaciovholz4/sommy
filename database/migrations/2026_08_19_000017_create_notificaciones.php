<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Centro de notificaciones del negocio: cada evento relevante (pedido nuevo,
 * venta cobrada, entrega, devolución, stock crítico, reposición) deja una
 * notificación resumida con su link para revisarlo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notificaciones')) {
            Schema::create('notificaciones', function (Blueprint $table) {
                $table->id();
                $table->string('tipo', 40)->index();       // pedido, venta, cobro, entrega, devolucion, stock, reposicion
                $table->string('titulo', 150);
                $table->string('mensaje', 300)->nullable();
                $table->string('url', 255)->nullable();    // link para revisar qué pasó
                $table->string('nivel', 15)->default('info'); // info | exito | alerta
                $table->timestamp('leida_at')->nullable()->index();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
