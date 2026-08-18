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
         Schema::create('variaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('option_type', 255);
            $table->boolean('status')->default(1);
            $table->timestamp('registration_date')->useCurrent()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variaciones');
    }
};
