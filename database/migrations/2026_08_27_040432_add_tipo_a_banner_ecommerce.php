<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El banner principal del home puede ser una imagen (como hasta ahora) o un
 * video. Todo lo que ya existe queda como 'imagen' (default).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banner_ecommerce', function (Blueprint $table) {
            $table->enum('tipo', ['imagen', 'video'])->default('imagen')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('banner_ecommerce', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
