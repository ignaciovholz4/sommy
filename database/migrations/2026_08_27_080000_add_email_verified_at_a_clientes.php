<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Verificación de correo para la cuenta de comprador (guard "cliente"):
 * requisito para poder finalizar una compra en el ecommerce.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('clientes', 'email_verified_at')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('clientes', 'email_verified_at')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropColumn('email_verified_at');
            });
        }
    }
};
