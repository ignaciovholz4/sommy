<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipos_comprobantes', function (Blueprint $table) {
            $table->id('idtipo_comprobante');
            $table->string('codigo', 5)->unique(); // Ej: FA, FB, FC, FE, NCA, NCB, NCC, TKT
            $table->string('descripcion');         // Ej: Factura A, Factura B, etc.
            $table->timestamps();
        });

        // Insertar tipos iniciales
        DB::table('tipos_comprobantes')->insert([
            ['codigo' => 'FA', 'descripcion' => 'Factura A'],
            ['codigo' => 'FB', 'descripcion' => 'Factura B'],
            ['codigo' => 'FC', 'descripcion' => 'Factura C'],
            ['codigo' => 'FE', 'descripcion' => 'Factura E (Exportación)'],
            ['codigo' => 'NCA', 'descripcion' => 'Nota de Crédito A'],
            ['codigo' => 'NCB', 'descripcion' => 'Nota de Crédito B'],
            ['codigo' => 'NCC', 'descripcion' => 'Nota de Crédito C'],
            ['codigo' => 'NDA', 'descripcion' => 'Nota de Débito A'],
            ['codigo' => 'NDB', 'descripcion' => 'Nota de Débito B'],
            ['codigo' => 'NDC', 'descripcion' => 'Nota de Débito C'],
            ['codigo' => 'TKT', 'descripcion' => 'Ticket Factura'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('tipos_comprobantes');
    }
};