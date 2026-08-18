<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('clientes', function (Blueprint $table) {
            // Ajout des colonnes manquantes
            $table->string('estatus', 50)->after('email')->default(''); 
            $table->integer('number_exterior')->nullable()->after('estatus');
            $table->string('number_interior', 100)->nullable()->after('number_exterior');
            $table->string('materno', 255)->nullable()->collation('utf8_general_ci')->after('number_interior');
            $table->string('paterno', 255)->nullable()->collation('utf8_general_ci')->after('materno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('clientes', function (Blueprint $table) {
            // Ajout des colonnes manquantes
            $table->string('estatus', 50)->after('email')->default(''); 
            $table->integer('number_exterior')->nullable()->after('estatus');
            $table->string('number_interior', 100)->nullable()->after('number_exterior');
            $table->string('materno', 255)->nullable()->collation('utf8_general_ci')->after('number_interior');
            $table->string('paterno', 255)->nullable()->collation('utf8_general_ci')->after('materno');
        });
    }
};
