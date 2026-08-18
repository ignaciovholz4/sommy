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
        Schema::create('status_orders_ecommerce', function (Blueprint $table) {
            $table->increments('status_id'); // PK autoincremental
            $table->string('status_name', 50)->unique(); // nombre único
            $table->boolean('active')->default(true); // activo/inactivo
            $table->timestamp('registration_date')->useCurrent(); // fecha de registro
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_orders_ecommerce');
    }
};