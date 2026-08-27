<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banner_ecommerce', function (Blueprint $table) {
            $table->string('titulo')->nullable()->after('name');
            $table->string('subtitulo')->nullable()->after('titulo');
            $table->string('boton_texto')->nullable()->after('subtitulo');
            $table->string('boton_url')->nullable()->after('boton_texto');
            $table->integer('orden')->default(0)->after('boton_url');
        });
    }

    public function down(): void
    {
        Schema::table('banner_ecommerce', function (Blueprint $table) {
            $table->dropColumn(['titulo', 'subtitulo', 'boton_texto', 'boton_url', 'orden']);
        });
    }
};
