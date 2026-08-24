<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La tabla gasto_categorias estaba vacía: sin categorías cargadas no hay
 * dónde imputar gastos de Ads/IA/Fletes ni nada más. Se cargan las básicas
 * para que el negocio pueda empezar a registrar gastos ya mismo; el usuario
 * puede renombrar/agregar otras desde el panel de Finanzas > Categorías.
 */
return new class extends Migration {
    public function up(): void
    {
        $nombres = [
            'Publicidad (Ads)',
            'Inteligencia Artificial (IA)',
            'Fletes y logística',
            'Alquiler',
            'Sueldos',
            'Servicios (luz, internet, etc.)',
            'Impuestos',
            'Insumos y mantenimiento',
            'Otros gastos',
        ];

        foreach ($nombres as $nombre) {
            $existe = DB::table('gasto_categorias')->where('nombre', $nombre)->exists();
            if (!$existe) {
                DB::table('gasto_categorias')->insert([
                    'nombre'     => $nombre,
                    'padre_id'   => null,
                    'activo'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('gasto_categorias')->whereIn('nombre', [
            'Publicidad (Ads)',
            'Inteligencia Artificial (IA)',
            'Fletes y logística',
            'Alquiler',
            'Sueldos',
            'Servicios (luz, internet, etc.)',
            'Impuestos',
            'Insumos y mantenimiento',
            'Otros gastos',
        ])->delete();
    }
};
