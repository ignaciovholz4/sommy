<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedBigInteger('proveedor_id')->nullable()->after('marca_id');
            $table->string('codigo_proveedor', 50)->nullable()->after('proveedor_id')->index();

            $table->foreign('proveedor_id')->references('idproveedor')->on('proveedores')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['proveedor_id']);
            $table->dropIndex(['codigo_proveedor']);
            $table->dropColumn(['proveedor_id', 'codigo_proveedor']);
        });
    }
};
