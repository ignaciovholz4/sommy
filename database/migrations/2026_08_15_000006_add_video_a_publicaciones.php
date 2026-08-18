<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Las publicaciones también pueden ser videos generados con IA (Veo). */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('publicaciones', 'video_final')) {
            Schema::table('publicaciones', function (Blueprint $table) {
                $table->string('video_final', 255)->nullable()->after('imagen_final');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('publicaciones', 'video_final')) {
            Schema::table('publicaciones', function (Blueprint $table) {
                $table->dropColumn('video_final');
            });
        }
    }
};
