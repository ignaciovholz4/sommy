<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 🔹 Insertar métodos iniciales
        DB::table('payment_methods')->insert([
            ['method_name' => 'efectivo', 'status' => true],
            ['method_name' => 'banco',    'status' => true],
            ['method_name' => 'tarjeta',  'status' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
