<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('color', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('hexadecimal')->nullable();
            $table->timestamp('registration_date')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('color');
    }
};
