<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pedidos sin abrir resaltados en amarillo en la grilla: visto_at se marca
 * la primera vez que alguien entra al detalle del pedido.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('order_ecommerce', 'visto_at')) {
            Schema::table('order_ecommerce', function (Blueprint $table) {
                $table->timestamp('visto_at')->nullable()->after('additional_info');
            });
        }
    }

    public function down(): void
    {
        Schema::table('order_ecommerce', function (Blueprint $table) {
            $table->dropColumn('visto_at');
        });
    }
};
