<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ventas MODIFY estado ENUM('a cobrar','cobrada','anulada') NOT NULL DEFAULT 'a cobrar'");
    }

    public function down(): void
    {
        // Revertimos a los valores originales
        DB::statement("ALTER TABLE ventas MODIFY estado ENUM('pendiente','aprobado','facturado') NOT NULL DEFAULT 'pendiente'");
    }
};