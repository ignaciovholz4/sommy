<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Check if ubicacion field exists before adding
            if (!Schema::hasColumn('productos', 'ubicacion')) {
                $table->string('ubicacion', 200)->nullable()->after('tipo_producto_id')->comment('Product location in warehouse');
            }
            
            // Check if marca field exists before adding
            if (!Schema::hasColumn('productos', 'marca')) {
                $table->string('marca', 100)->nullable()->after('ubicacion')->comment('Product brand');
            }
        });
        
        // Modify discount field to be percentage-based (0-100) if it exists
        if (Schema::hasColumn('productos', 'descuento')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->decimal('descuento', 5, 2)->change()->comment('Discount percentage (0-100)');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Only drop columns if they exist
            if (Schema::hasColumn('productos', 'ubicacion')) {
                $table->dropColumn('ubicacion');
            }
            if (Schema::hasColumn('productos', 'marca')) {
                $table->dropColumn('marca');
            }
        });
        
        // Revert discount field change if it exists
        if (Schema::hasColumn('productos', 'descuento')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->decimal('descuento', 11, 2)->change();
            });
        }
    }
}; 