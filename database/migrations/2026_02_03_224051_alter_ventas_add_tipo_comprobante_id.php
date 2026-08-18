<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // 1. Eliminamos la columna antigua si ya no la necesitamos
            if (Schema::hasColumn('ventas', 'tipo_comprobante')) {
                $table->dropColumn('tipo_comprobante');
            }

            // 2. Creamos la nueva FK
            $table->unsignedBigInteger('tipo_comprobante_id')->after('cliente_id');
            $table->foreign('tipo_comprobante_id')
                  ->references('idtipo_comprobante')
                  ->on('tipos_comprobantes')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // revertimos cambios
            $table->dropForeign(['tipo_comprobante_id']);
            $table->dropColumn('tipo_comprobante');

            // restauramos la columna antigua
            $table->string('tipo_comprobante')->nullable();
        });
    }
};

