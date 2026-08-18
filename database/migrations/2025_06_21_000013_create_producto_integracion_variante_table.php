<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('producto_integracion_variante', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('producto_id')->nullable();
            $table->integer('variacion_id')->nullable();
            $table->integer('variante_id')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('status')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamp('registration_date')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto_integracion_variante');
    }
};
