<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained('price_lists')->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            // For type 'amount': explicit price per product
            $table->decimal('amount_price', 11, 2)->nullable();
            // For type 'product_percentage': unique percentage per product
            $table->enum('value_type', ['discount', 'increase'])->nullable();
            $table->decimal('percentage', 8, 2)->nullable();
            // Purchase price fields
            $table->decimal('purchase_price', 11, 2)->nullable();
            $table->enum('purchase_value_type', ['discount', 'increase'])->nullable();
            $table->decimal('purchase_percentage', 8, 2)->nullable();
            $table->timestamps();

            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
    }
};

