<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Doble nombre del artículo: `nombre` es el comercial (ventas, pedidos web,
 * presupuestos, ecommerce) y `nombre_compra` es como lo llama el proveedor
 * (se usa en compras y pedidos de compra). Si está vacío, se usa el comercial.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('productos', 'nombre_compra')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('nombre_compra', 200)->nullable()->after('nombre');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'nombre_compra')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('nombre_compra');
            });
        }
    }
};
