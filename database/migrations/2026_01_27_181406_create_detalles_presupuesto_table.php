<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_presupuesto', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('presupuesto_id');
            $table->foreign('presupuesto_id')
                ->references('idpresupuesto')
                ->on('presupuestos')
                ->onDelete('cascade');

            $table->unsignedBigInteger('idarticulo');
            $table->foreign('idarticulo')
                ->references('idarticulo')
                ->on('productos')
                ->onDelete('cascade');

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);

            $table->decimal('iva', 5, 2)->default(0);
            $table->decimal('subtotal_neto', 12, 2)->default(0);
            $table->decimal('subtotal_con_iva', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_presupuesto');
    }
};