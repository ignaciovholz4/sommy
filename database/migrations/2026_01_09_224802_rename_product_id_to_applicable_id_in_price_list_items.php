<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Use raw SQL for MariaDB compatibility
        if (Schema::hasColumn('price_list_items', 'product_id')) {
            \DB::statement('ALTER TABLE `price_list_items` CHANGE `product_id` `applicable_id` BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('price_list_items', function (Blueprint $table) {
            if (!Schema::hasColumn('price_list_items', 'applicable_type')) {
                $table->string('applicable_type')->default('articulo')->after('applicable_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('price_list_items', function (Blueprint $table) {
            if (Schema::hasColumn('price_list_items', 'applicable_type')) {
                $table->dropColumn('applicable_type');
            }
            if (Schema::hasColumn('price_list_items', 'applicable_id')) {
                $table->renameColumn('applicable_id', 'product_id');
            }
        });
    }
};
