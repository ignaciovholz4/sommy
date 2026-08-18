<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // // Cambiar columna estado en ventas
        // Schema::table('ventas', function (Blueprint $table) {
        //     $table->enum('estado', ['pendiente', 'aprobado', 'facturado'])
        //           ->default('pendiente')
        //           ->change();
        // });

        // // Cambiar columna estado en compras
        // Schema::table('compras', function (Blueprint $table) {
        //     $table->enum('estado', ['pendiente', 'aprobado', 'facturado'])
        //           ->default('pendiente')
        //           ->change();
        // });
    }

    public function down()
    {
        // Volver al enum anterior (por si se hace rollback)
        // Schema::table('ventas', function (Blueprint $table) {
        //     $table->enum('estado', ['borrador', 'confirmado', 'anulado'])
        //           ->default('borrador')
        //           ->change();
        // });

        // Schema::table('compras', function (Blueprint $table) {
        //     $table->enum('estado', ['borrador', 'confirmado', 'anulado'])
        //           ->default('borrador')
        //           ->change();
        // });
    }
};