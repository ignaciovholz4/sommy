<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['amount', 'product_percentage', 'general_percentage']);
            $table->enum('context', ['sales', 'purchase'])->default('sales');
            $table->enum('value_type', ['discount', 'increase'])->nullable();
            $table->decimal('percentage', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_lists');
    }
};

