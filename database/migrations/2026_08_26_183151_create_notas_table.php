<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notas recordatorias: sueltas (tablero general, notable_type/id nulos) o
 * pegadas a un cliente/proveedor/venta/compra puntual. fecha_recordatorio
 * es opcional — si se carga, la nota aparece como "vencida" cuando pasa esa
 * fecha y sigue sin completarse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->text('contenido');
            $table->date('fecha_recordatorio')->nullable();
            $table->boolean('completada')->default(false);
            $table->string('notable_type')->nullable();
            $table->unsignedBigInteger('notable_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index(['notable_type', 'notable_id']);
            $table->index(['completada', 'fecha_recordatorio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
