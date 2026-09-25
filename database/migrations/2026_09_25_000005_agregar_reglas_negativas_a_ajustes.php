<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reglas negativas extra opcionales del usuario, se suman a las fijas de NegativeRulesBuilder. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones_ajustes', function (Blueprint $table) {
            if (!Schema::hasColumn('publicaciones_ajustes', 'reglas_negativas_extra')) {
                $table->text('reglas_negativas_extra')->nullable()->after('estilo_imagen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones_ajustes', function (Blueprint $table) {
            if (Schema::hasColumn('publicaciones_ajustes', 'reglas_negativas_extra')) {
                $table->dropColumn('reglas_negativas_extra');
            }
        });
    }
};
