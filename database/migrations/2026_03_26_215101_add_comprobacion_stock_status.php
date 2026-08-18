<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Borrar todos los estados existentes
        DB::table('status_orders_ecommerce')->delete();

        // Insertar estados en orden correcto
        DB::table('status_orders_ecommerce')->insert([
            ['status_id' => 1, 'status_name' => 'Pendiente',             'active' => 1],
            ['status_id' => 2, 'status_name' => 'Comprobación de stock', 'active' => 1],
            ['status_id' => 3, 'status_name' => 'Pagado',                'active' => 1],
            ['status_id' => 4, 'status_name' => 'Enviado',               'active' => 1],
            ['status_id' => 5, 'status_name' => 'Entregado',             'active' => 1],
            ['status_id' => 6, 'status_name' => 'Cancelado',             'active' => 1],
        ]);
    }

    public function down(): void
    {
        DB::table('status_orders_ecommerce')->delete();

        DB::table('status_orders_ecommerce')->insert([
            ['status_id' => 1, 'status_name' => 'Pendiente', 'active' => 1],
            ['status_id' => 2, 'status_name' => 'Pagado',    'active' => 1],
            ['status_id' => 3, 'status_name' => 'Enviado',   'active' => 1],
            ['status_id' => 4, 'status_name' => 'Entregado', 'active' => 1],
            ['status_id' => 5, 'status_name' => 'Cancelado', 'active' => 1],
        ]);
    }
};