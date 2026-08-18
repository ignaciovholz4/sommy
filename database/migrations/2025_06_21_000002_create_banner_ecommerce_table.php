<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('banner_ecommerce', function (Blueprint $table) {
            $table->increments('banner_id');
            $table->string('name')->nullable();
            $table->string('name_image')->nullable();
            $table->string('description')->nullable();
            $table->dateTime('banner_date')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('name_image_movil')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('banner_ecommerce');
    }
};
