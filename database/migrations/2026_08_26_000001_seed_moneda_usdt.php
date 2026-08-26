<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // "USDT" no entra en el string(3) original (pensado para ISO ARS/USD).
        // Raw SQL en vez de Blueprint::change() porque el proyecto no tiene doctrine/dbal instalado.
        DB::statement('ALTER TABLE monedas MODIFY codigo VARCHAR(10) NOT NULL');

        if (!DB::table('monedas')->where('codigo', 'USDT')->exists()) {
            DB::table('monedas')->insert([
                'codigo' => 'USDT',
                'nombre' => 'Tether (USDT)',
                'simbolo' => '₮',
                'decimales' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('monedas')->where('codigo', 'USDT')->delete();
    }
};
