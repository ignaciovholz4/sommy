<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuantas unidades gratis de ese regalo se llevan (ej. 2 almohadas de
 * regalo al comprar un colchon). Por defecto 1, como era hasta ahora.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('producto_regalos', 'cantidad')) {
            Schema::table('producto_regalos', function (Blueprint $table) {
                $table->unsignedTinyInteger('cantidad')->default(1)->after('regalo_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('producto_regalos', function (Blueprint $table) {
            $table->dropColumn('cantidad');
        });
    }
};
