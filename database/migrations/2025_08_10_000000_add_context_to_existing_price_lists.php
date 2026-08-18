<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add context column to price_lists table if it doesn't exist
        if (!Schema::hasColumn('price_lists', 'context')) {
            Schema::table('price_lists', function (Blueprint $table) {
                $table->enum('context', ['sales', 'purchase'])->default('sales')->after('type');
            });
        }

        // Add purchase price columns to price_list_items table if they don't exist
        if (!Schema::hasColumn('price_list_items', 'purchase_price')) {
            Schema::table('price_list_items', function (Blueprint $table) {
                $table->decimal('purchase_price', 11, 2)->nullable()->after('percentage');
                $table->enum('purchase_value_type', ['discount', 'increase'])->nullable()->after('purchase_price');
                $table->decimal('purchase_percentage', 8, 2)->nullable()->after('purchase_value_type');
            });
        }

        // Update existing price lists to have 'sales' context by default
        DB::table('price_lists')->whereNull('context')->update(['context' => 'sales']);
    }

    public function down(): void
    {
        // Remove purchase price columns from price_list_items table
        if (Schema::hasColumn('price_list_items', 'purchase_price')) {
            Schema::table('price_list_items', function (Blueprint $table) {
                $table->dropColumn(['purchase_price', 'purchase_value_type', 'purchase_percentage']);
            });
        }

        // Remove context column from price_lists table
        if (Schema::hasColumn('price_lists', 'context')) {
            Schema::table('price_lists', function (Blueprint $table) {
                $table->dropColumn('context');
            });
        }
    }
}; 