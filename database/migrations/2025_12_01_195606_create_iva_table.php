<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('iva', function (Blueprint $table) {
            $table->increments('idiva');
            $table->string('tipo_iva', 100); // descripción del IVA
            $table->decimal('value_iva', 5, 2); // porcentaje de IVA
            $table->string('value_arca', 50); // identificador para API Arca
        });
    }

    public function down()
    {
        Schema::dropIfExists('iva');
    }
};