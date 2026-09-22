<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hasta ahora el stock de un pedido ecommerce solo se descontaba al
 * confirmar el pago (OrderController::update_paid). Si el flete ya salió
 * con la mercadería pero el pago todavía no se confirmó (transferencia
 * pendiente, pago contra entrega, etc.), el stock seguía mostrando la
 * cantidad vieja aunque físicamente ya no estuviera — riesgo real de
 * vender de más. Esta columna marca CUÁNDO se descontó el stock de un
 * pedido (por el primer evento que ocurra: pago confirmado o despacho del
 * flete), para no descontarlo dos veces si pasan los dos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            if (!Schema::hasColumn('order_ecommerce', 'stock_descontado_at')) {
                $table->timestamp('stock_descontado_at')->nullable()->after('purchase_capi_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            if (Schema::hasColumn('order_ecommerce', 'stock_descontado_at')) {
                $table->dropColumn('stock_descontado_at');
            }
        });
    }
};
