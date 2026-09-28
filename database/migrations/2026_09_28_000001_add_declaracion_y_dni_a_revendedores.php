<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('revendedores', function (Blueprint $table) {
            $table->string('dni_foto', 255)->nullable()->after('titular_cuenta');
            $table->boolean('declaracion_independiente')->default(false)->after('dni_foto');
            $table->timestamp('declaracion_aceptada_at')->nullable()->after('declaracion_independiente');
        });
    }

    public function down(): void
    {
        Schema::table('revendedores', function (Blueprint $table) {
            $table->dropColumn(['dni_foto', 'declaracion_independiente', 'declaracion_aceptada_at']);
        });
    }
};
