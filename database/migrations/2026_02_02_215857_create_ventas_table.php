<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('idventa');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes', 'idcliente')->onDelete('cascade');

            $table->string('tipo_comprobante')->nullable();
            $table->string('num_folio')->unique(); // folio de la venta

            $table->date('fecha');
            $table->enum('estado', ['pendiente', 'aprobado', 'facturado'])->default('pendiente');

            $table->decimal('total_neto', 12, 2)->default(0);
            $table->decimal('total_con_iva', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};