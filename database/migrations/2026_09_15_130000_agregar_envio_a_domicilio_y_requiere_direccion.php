<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El checkout solo tenía "Retiro en local" como zona de envío: no existía
 * forma de pedir entrega a domicilio ni de saber si hacía falta la
 * dirección completa (calle/localidad/provincia) para esa zona en
 * particular. Se agrega:
 * - zonas_envio.requiere_direccion: si la zona necesita que el cliente
 *   complete su dirección (el retiro en local no la necesita).
 * - zonas_envio.costo ahora admite NULL = "a coordinar" (el costo real de
 *   envío a domicilio depende de la zona/peso y lo confirma un vendedor por
 *   WhatsApp, no es un monto fijo que podamos calcular acá).
 * - Se agrega la zona real "Envío a domicilio" con costo a coordinar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zonas_envio', function (Blueprint $table) {
            if (!Schema::hasColumn('zonas_envio', 'requiere_direccion')) {
                $table->boolean('requiere_direccion')->default(true)->after('costo');
            }
        });

        DB::statement('ALTER TABLE zonas_envio MODIFY costo DECIMAL(10,2) NULL');

        DB::table('zonas_envio')->where('nombre', 'like', '%retiro%')->update(['requiere_direccion' => false]);

        if (!DB::table('zonas_envio')->where('nombre', 'Envío a domicilio')->exists()) {
            DB::table('zonas_envio')->insert([
                'nombre' => 'Envío a domicilio',
                'costo' => null,
                'requiere_direccion' => true,
                'activo' => true,
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('zonas_envio')->where('nombre', 'Envío a domicilio')->delete();

        Schema::table('zonas_envio', function (Blueprint $table) {
            if (Schema::hasColumn('zonas_envio', 'requiere_direccion')) {
                $table->dropColumn('requiere_direccion');
            }
        });
    }
};
