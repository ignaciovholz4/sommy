<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            if (!Schema::hasColumn('proveedores', 'cuit')) {
                $table->string('cuit', 20)->nullable();
            }
            if (!Schema::hasColumn('proveedores', 'condicion_pago_dias')) {
                // Dias de plazo para pagar (0 = contado)
                $table->unsignedSmallInteger('condicion_pago_dias')->default(0);
            }
            if (!Schema::hasColumn('proveedores', 'notas')) {
                $table->text('notas')->nullable();
            }
            if (!Schema::hasColumn('proveedores', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn(['cuit', 'condicion_pago_dias', 'notas', 'created_at', 'updated_at']);
        });
    }
};
