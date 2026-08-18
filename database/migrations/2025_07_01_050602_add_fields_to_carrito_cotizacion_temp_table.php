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
            Schema::table('carrito_cotizacion_temp', function (Blueprint $table) {
            $table->unsignedInteger('id_user')->after('id');
            $table->unsignedInteger('producto_id')->after('id_user');
            $table->string('cod', 30)->after('producto_id');
            $table->string('nombre', 255)->after('cod');
            $table->decimal('cantidad', 11, 2)->after('nombre');
            $table->string('descipcion', 250)->nullable()->after('cantidad');
            $table->decimal('precio', 11, 2)->after('descipcion');
            $table->decimal('descuento', 11, 2)->after('precio');
            $table->decimal('total', 11, 2)->after('descuento');
  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carrito_cotizacion_temp', function (Blueprint $table) {
            $table->dropColumn([
                'id_user',
                'producto_id',
                'cod',
                'nombre',
                'cantidad',
                'descipcion',
                'precio',
                'descuento',
                'total',
                'created_at',
                'updated_at',
            ]);
        });
    }
};
