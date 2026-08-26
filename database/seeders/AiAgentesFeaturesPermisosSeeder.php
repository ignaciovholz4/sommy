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
            // Reposición inteligente de stock
            ['slug' => 'compras.reposicion.index', 'name' => 'ver reposición de stock', 'description' => 'Puede ver los ajustes y el estado de la reposición inteligente'],
            ['slug' => 'compras.reposicion.generar', 'name' => 'generar pedidos de reposición', 'description' => 'Puede generar pedidos de compra sugeridos manualmente'],
            ['slug' => 'compras.reposicion.ajustar', 'name' => 'ajustar parámetros de reposición', 'description' => 'Puede editar los ajustes de la reposición inteligente'],
            // Chat de Reportes (analista IA)
            ['slug' => 'reportes.chat.index', 'name' => 'ver historial del chat de Reportes', 'description' => 'Puede ver el historial de conversaciones con el analista IA'],
            ['slug' => 'reportes.chat.usar', 'name' => 'usar el chat de Reportes', 'description' => 'Puede hacerle preguntas al analista IA sobre ventas, margen, stock y deudores'],
            // Cobranzas (cola de aprobación de recordatorios)
            ['slug' => 'finanzas.cobranzas.index', 'name' => 'ver cola de cobranzas', 'description' => 'Puede ver los recordatorios de cobranza pendientes de revisión'],
            ['slug' => 'finanzas.cobranzas.aprobar', 'name' => 'aprobar cobranza pendiente', 'description' => 'Puede aprobar recordatorios de cobranza y enviarlos por WhatsApp'],
            ['slug' => 'finanzas.cobranzas.descartar', 'name' => 'descartar cobranzas', 'description' => 'Puede descartar un recordatorio de cobranza pendiente'],
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
