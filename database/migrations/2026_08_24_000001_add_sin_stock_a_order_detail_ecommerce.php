<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite que un cliente pida un producto aunque no haya stock (queda "a consultar",
 * no se bloquea ni se reserva). sin_stock se calcula del lado del servidor al crear
 * el pedido (no se confía en lo que mande el navegador) comparando la cantidad
 * pedida contra el stock real disponible en ese momento.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_detail_ecommerce', function (Blueprint $table) {
            $table->boolean('sin_stock')->default(false)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('order_detail_ecommerce', function (Blueprint $table) {
            $table->dropColumn('sin_stock');
        });
    }
};
