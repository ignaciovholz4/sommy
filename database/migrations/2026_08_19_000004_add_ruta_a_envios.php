<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Datos de ruteo del envío: coordenadas geocodificadas y orden en la hoja de ruta. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            if (!Schema::hasColumn('envios', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('direccion_entrega');
            }
            if (!Schema::hasColumn('envios', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
            if (!Schema::hasColumn('envios', 'orden_ruta')) {
                $table->unsignedSmallInteger('orden_ruta')->nullable()->after('lng');
            }
        });
    }

    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            foreach (['lat', 'lng', 'orden_ruta'] as $col) {
                if (Schema::hasColumn('envios', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
