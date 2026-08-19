<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portal del fletero: acceso por link con token (sin contraseña) y registro
 * de cada entrega hecha en la puerta — monto cobrado, firma del cliente en
 * pantalla y foto del efectivo. La plata queda "a rendir" hasta que la caja
 * la registre con el flujo normal de cobro.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transportistas', 'token_acceso')) {
            Schema::table('transportistas', function (Blueprint $table) {
                $table->string('token_acceso', 64)->nullable()->unique()->after('activo');
            });
        }

        if (!Schema::hasTable('entregas_fletero')) {
            Schema::create('entregas_fletero', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('envio_id')->index();
                $table->unsignedBigInteger('transportista_id')->index();
                $table->decimal('monto_cobrado', 14, 2)->default(0);
                $table->string('firma_path', 255)->nullable();
                $table->string('foto_plata_path', 255)->nullable();
                $table->string('nota', 300)->nullable();
                $table->boolean('rendido')->default(false); // la caja ya registró esta plata
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas_fletero');
        if (Schema::hasColumn('transportistas', 'token_acceso')) {
            Schema::table('transportistas', function (Blueprint $table) {
                $table->dropColumn('token_acceso');
            });
        }
    }
};
