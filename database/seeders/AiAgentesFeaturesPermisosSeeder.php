<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Permission\Models\Role;
use App\Permission\Models\Permission;

/**
 * Permisos de las 4 features de IA (comprobantes OCR, reposicion de stock,
 * chat de reportes, cobranzas). Idempotente: puede correrse varias veces
 * (php artisan db:seed --class=AiAgentesFeaturesPermisosSeeder).
 */
class AiAgentesFeaturesPermisosSeeder extends Seeder
{
    public function run()
    {
        $permisos = [
            // Comprobantes con IA (compras)
            ['slug' => 'compras.ocr_ia', 'name' => 'cargar comprobantes con IA', 'description' => 'Puede subir facturas/remitos de proveedor para precargar el alta de compra con IA'],
        ];

        $ids = [];
        foreach ($permisos as $p) {
            $permiso = Permission::firstOrCreate(['slug' => $p['slug']], $p);
            $ids[] = $permiso->id;
        }

        $admin = Role::where('slug', 'Admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching($ids);
        }
    }
}
