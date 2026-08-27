<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La galería de un producto puede tener fotos y videos mezclados. Todo lo
 * que ya existe queda como 'imagen' (default).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto_imagenes', function (Blueprint $table) {
            $table->enum('tipo', ['imagen', 'video'])->default('imagen')->after('combinacion_id');
        });
    }

    public function down(): void
    {
        Schema::table('producto_imagenes', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
