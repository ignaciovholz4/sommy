<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('revendedores', function (Blueprint $table) {
            $table->string('firma', 255)->nullable()->after('declaracion_aceptada_at');
        });
    }

    public function down(): void
    {
        Schema::table('revendedores', function (Blueprint $table) {
            $table->dropColumn('firma');
        });
    }
};
