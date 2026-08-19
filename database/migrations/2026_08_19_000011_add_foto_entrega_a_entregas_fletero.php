<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto de la casa/lugar donde el fletero entrego el pedido, ademas de la
 * firma y la foto del efectivo que ya se registraban.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entregas_fletero', function (Blueprint $table) {
            $table->string('foto_entrega_path', 255)->nullable()->after('foto_plata_path');
        });
    }

    public function down(): void
    {
        Schema::table('entregas_fletero', function (Blueprint $table) {
            $table->dropColumn('foto_entrega_path');
        });
    }
};
