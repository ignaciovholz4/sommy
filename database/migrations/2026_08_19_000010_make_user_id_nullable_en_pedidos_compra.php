<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La reposicion inteligente genera pedidos de compra sin un usuario detras
 * (corre por comando programado): user_id pasa a ser opcional.
 * Se usa SQL crudo para el MODIFY porque el proyecto no tiene doctrine/dbal
 * instalado (requerido por el metodo ->change() de los migrations).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_compra', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE pedidos_compra MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('pedidos_compra', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_compra', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE pedidos_compra MODIFY user_id BIGINT UNSIGNED NOT NULL');

        Schema::table('pedidos_compra', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
