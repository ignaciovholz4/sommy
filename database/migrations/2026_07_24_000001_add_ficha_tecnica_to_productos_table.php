<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Ficha técnica de colchón — todos nullable (complementos como almohadas no los usan)
            $table->string('tipo_colchon', 30)->nullable()->after('descuento');
            $table->string('firmeza', 20)->nullable()->after('tipo_colchon');
            $table->decimal('altura_cm', 5, 1)->nullable()->after('firmeza');
            $table->decimal('densidad_kg_m3', 6, 2)->nullable()->after('altura_cm');
            $table->string('plazas', 10)->nullable()->after('densidad_kg_m3');
            $table->unsignedSmallInteger('peso_max_kg')->nullable()->after('plazas');
            $table->unsignedTinyInteger('garantia_anios')->nullable()->after('peso_max_kg');
            $table->unsignedSmallInteger('noches_prueba')->nullable()->after('garantia_anios');
            $table->string('certificaciones', 500)->nullable()->after('noches_prueba');
            $table->boolean('pillow_top')->nullable()->after('certificaciones');
            $table->string('tela', 100)->nullable()->after('pillow_top');
        });

        // descripcion pasa de varchar(500) a TEXT (sin doctrine/dbal no se puede usar ->change())
        DB::statement('ALTER TABLE productos MODIFY descripcion TEXT NULL');
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_colchon', 'firmeza', 'altura_cm', 'densidad_kg_m3', 'plazas',
                'peso_max_kg', 'garantia_anios', 'noches_prueba', 'certificaciones',
                'pillow_top', 'tela',
            ]);
        });

        DB::statement('ALTER TABLE productos MODIFY descripcion VARCHAR(500) NOT NULL');
    }
};
