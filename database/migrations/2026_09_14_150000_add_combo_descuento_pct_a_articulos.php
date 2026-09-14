<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('combo_descuento_pct', 5, 2)->nullable()->default(0)
                ->after('pventa_mayorista')
                ->comment('% de descuento al llevar este producto en combo con sus relacionados (0 = sin armador de combo)');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('combo_descuento_pct');
        });
    }
};
