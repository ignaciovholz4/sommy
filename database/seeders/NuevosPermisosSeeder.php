<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Permission\Models\Role;
use App\Permission\Models\Permission;

/**
 * Permisos de los modulos nuevos: WhatsApp/CRM, agentes IA y finanzas.
 * Idempotente: puede correrse varias veces (php artisan db:seed --class=NuevosPermisosSeeder).
 */
class NuevosPermisosSeeder extends Seeder
{
    public function run()
    {
        $permisos = [
            // WhatsApp / CRM
            ['slug' => 'whatsapp.index',          'name' => 'ver la bandeja de WhatsApp',            'description' => 'Puede ver la bandeja de conversaciones de WhatsApp'],
            ['slug' => 'whatsapp.send',           'name' => 'responder por WhatsApp',                'description' => 'Puede enviar mensajes desde la bandeja'],
            ['slug' => 'whatsapp.assign',         'name' => 'asignar conversaciones',                'description' => 'Puede asignar conversaciones a otros vendedores'],
            ['slug' => 'whatsapp.confirm_order',  'name' => 'confirmar pedidos del bot',             'description' => 'Puede confirmar borradores de pedido creados por el agente IA'],
            ['slug' => 'whatsapp.templates',      'name' => 'gestionar plantillas de WhatsApp',      'description' => 'Puede administrar plantillas HSM de Meta'],
            // Agentes IA
            ['slug' => 'agents.index',            'name' => 'ver agentes IA',                        'description' => 'Puede ver la lista de agentes de venta IA'],
            ['slug' => 'agents.manage',           'name' => 'gestionar agentes IA',                  'description' => 'Puede crear y configurar agentes de venta IA'],
            // Finanzas
            ['slug' => 'finanzas.gastos.index',   'name' => 'ver gastos',                            'description' => 'Puede ver el listado de gastos'],
            ['slug' => 'finanzas.gastos.manage',  'name' => 'gestionar gastos',                      'description' => 'Puede crear, editar y eliminar gastos y categorias'],
            ['slug' => 'finanzas.envios.index',   'name' => 'ver envios y fletes',                   'description' => 'Puede ver el listado de envios'],
            ['slug' => 'finanzas.envios.manage',  'name' => 'gestionar envios y fletes',             'description' => 'Puede crear envios y actualizar su estado'],
            ['slug' => 'finanzas.transportistas.manage', 'name' => 'gestionar transportistas',       'description' => 'Puede administrar transportistas'],
            ['slug' => 'finanzas.cxp.index',      'name' => 'ver cuentas por pagar',                 'description' => 'Puede ver la cuenta corriente de proveedores'],
            ['slug' => 'finanzas.cxp.manage',     'name' => 'gestionar cuentas por pagar',           'description' => 'Puede registrar deudas y pagos a proveedores'],
            ['slug' => 'finanzas.dashboard',      'name' => 'ver tablero financiero',                'description' => 'Puede ver el tablero de flujo de caja y resultados'],
            ['slug' => 'finanzas.chytapay.manage', 'name' => 'gestionar conexion con Chytapay',      'description' => 'Puede conectar/desconectar cuentas a Chytapay y sincronizar cobros'],
            ['slug' => 'finanzas.cheques.index',  'name' => 'ver cartera de cheques',                'description' => 'Puede ver el listado de cheques propios y de terceros'],
            ['slug' => 'finanzas.cheques.manage', 'name' => 'gestionar cartera de cheques',          'description' => 'Puede depositar, acreditar, rechazar y anular cheques'],
            ['slug' => 'finanzas.divisas.index',  'name' => 'ver compra/venta de divisas',           'description' => 'Puede ver el historial de compra/venta de moneda extranjera'],
            ['slug' => 'finanzas.divisas.manage', 'name' => 'gestionar compra/venta de divisas',     'description' => 'Puede registrar compras y ventas de moneda extranjera'],
            ['slug' => 'finanzas.marketing.index', 'name' => 'ver panel de Meta/Google Ads',         'description' => 'Puede ver el gasto publicitario de Meta Ads y Google Ads'],
        ];

        $ids = [];
        foreach ($permisos as $p) {
            $permiso = Permission::firstOrCreate(['slug' => $p['slug']], $p);
            $ids[] = $permiso->id;
        }

        // Adjuntar todos al rol Admin (ademas del full-access que ya tiene)
        $admin = Role::where('slug', 'Admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching($ids);
        }
    }
}
