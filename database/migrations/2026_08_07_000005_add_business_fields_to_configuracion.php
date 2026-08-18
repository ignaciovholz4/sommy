<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->string('cuit', 13)->nullable()->after('phone');
            $table->string('razon_social', 200)->nullable()->after('cuit');
            $table->string('cbu', 22)->nullable()->after('razon_social');
            $table->string('alias_cbu', 50)->nullable()->after('cbu');
            $table->decimal('descuento_transferencia', 5, 2)->default(0)->after('alias_cbu');
            $table->string('whatsapp', 20)->nullable()->after('descuento_transferencia');
        });
    }

    public function down()
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn(['cuit', 'razon_social', 'cbu', 'alias_cbu', 'descuento_transferencia', 'whatsapp']);
        });
    }
};
