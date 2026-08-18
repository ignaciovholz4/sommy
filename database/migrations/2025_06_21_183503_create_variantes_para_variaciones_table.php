<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('variantes_para_variaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('variacion_id')->unsigned();
            $table->string('name');
            $table->string('option_type')->nullable();
            $table->string('descripcion')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('registration_date')->useCurrent()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variantes_para_variaciones');
    }
};
