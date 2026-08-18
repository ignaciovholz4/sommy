<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ajuste en movimientos
        Schema::table('movimientos', function (Blueprint $table) {
            // Si existe caja_id, lo eliminamos
            if (Schema::hasColumn('movimientos', 'caja_id')) {
                $table->dropForeign(['caja_id']);
                $table->dropColumn('caja_id');
            }

            // Agregamos cuenta_id
            if (!Schema::hasColumn('movimientos', 'cuenta_id')) {
                $table->foreignId('cuenta_id')
                      ->after('id')
                      ->constrained('cuentas')
                      ->cascadeOnDelete();
            }

            // Mantener caja_apertura_id para las cuentas de tipo caja
            if (!Schema::hasColumn('movimientos', 'caja_apertura_id')) {
                $table->foreignId('caja_apertura_id')
                      ->nullable()
                      ->constrained('caja_aperturas')
                      ->nullOnDelete();
            }
        });

        // Ajuste en caja_aperturas
        Schema::table('caja_aperturas', function (Blueprint $table) {
            // Si existe caja_id, lo eliminamos
            if (Schema::hasColumn('caja_aperturas', 'caja_id')) {
                $table->dropForeign(['caja_id']);
                $table->dropColumn('caja_id');
            }

            // Agregamos cuenta_id
            if (!Schema::hasColumn('caja_aperturas', 'cuenta_id')) {
                $table->foreignId('cuenta_id')
                      ->after('id')
                      ->constrained('cuentas')
                      ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Revertir cambios en movimientos
        Schema::table('movimientos', function (Blueprint $table) {
            if (Schema::hasColumn('movimientos', 'cuenta_id')) {
                $table->dropForeign(['cuenta_id']);
                $table->dropColumn('cuenta_id');
            }

            $table->foreignId('caja_id')
                  ->constrained('cajas')
                  ->cascadeOnDelete();
        });

        // Revertir cambios en caja_aperturas
        Schema::table('caja_aperturas', function (Blueprint $table) {
            if (Schema::hasColumn('caja_aperturas', 'cuenta_id')) {
                $table->dropForeign(['cuenta_id']);
                $table->dropColumn('cuenta_id');
            }

            $table->foreignId('caja_id')
                  ->constrained('cajas')
                  ->cascadeOnDelete();
        });
    }
};