<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE compras MODIFY estado ENUM('a pagar','pagada','anulada') NOT NULL DEFAULT 'a pagar'");
    }

    public function down(): void
    {
        // Revertimos a los valores originales
        DB::statement("ALTER TABLE compras MODIFY estado ENUM('pendiente','aprobado','facturado') NOT NULL DEFAULT 'pendiente'");
    }
};