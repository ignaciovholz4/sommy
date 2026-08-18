<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('capturarinventario', function (Blueprint $table) {
            $table->bigIncrements('idcaptura');
            $table->time('hora');
        });
    }

    public function down()
    {
        Schema::dropIfExists('capturarinventario');
    }
};
