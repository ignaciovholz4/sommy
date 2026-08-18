<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('dni_cuit', 13)->nullable()->after('email');
            $table->string('condicion_fiscal', 30)->nullable()->default('consumidor_final')->after('dni_cuit');
            $table->string('localidad', 100)->nullable()->after('direccion');
            $table->string('provincia', 60)->nullable()->after('localidad');
            $table->string('codigo_postal', 10)->nullable()->after('provincia');
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['dni_cuit', 'condicion_fiscal', 'localidad', 'provincia', 'codigo_postal']);
        });
    }
};
