<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Las devoluciones también pueden originarse en pedidos ecommerce/multicanal.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE devoluciones MODIFY tipo ENUM('venta','compra','pedido') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE devoluciones MODIFY tipo ENUM('venta','compra') NOT NULL");
    }
};
