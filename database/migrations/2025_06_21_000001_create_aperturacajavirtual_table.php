<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('aperturacajavirtual', function (Blueprint $table) {
            $table->bigIncrements('caja_virtual_id');
            $table->decimal('initial_amount', 15, 2)->nullable();
            $table->decimal('final_amount', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->dateTime('start_date_time')->nullable();
            $table->dateTime('end_date_time')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->enum('status_box', ['virtual_box_open','virtual_box_closed'])->default('virtual_box_open');
        });
    }

    public function down()
    {
        Schema::dropIfExists('aperturacajavirtual');
    }
};
