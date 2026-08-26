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
            ['slug' => 'whatsapp.templates',      'name' => 'gestionar plantillas de WhatsApp',      'description' => 'Puede administrar plantillas HSM de Meta'],
            ['slug' => 'whatsapp.note',           'name' => 'agregar notas internas',                'description' => 'Puede agregar notas internas a una conversación'],
            ['slug' => 'whatsapp.set_status',     'name' => 'cambiar estado de conversación',        'description' => 'Puede cambiar el estado de una conversación de WhatsApp'],
            ['slug' => 'whatsapp.toggle_mode',    'name' => 'pasar conversación a humano/bot',       'description' => 'Puede tomar o devolver el control de una conversación al bot'],
            ['slug' => 'whatsapp.link_cliente',   'name' => 'vincular cliente a conversación',       'description' => 'Puede vincular o crear un cliente desde una conversación'],
            ['slug' => 'whatsapp.tag',            'name' => 'etiquetar conversaciones',              'description' => 'Puede poner/quitar etiquetas a una conversación'],
            ['slug' => 'whatsapp.tags.store',     'name' => 'crear etiquetas',                       'description' => 'Puede crear una etiqueta nueva'],
            ['slug' => 'whatsapp.tags.destroy',   'name' => 'eliminar etiquetas',                    'description' => 'Puede eliminar una etiqueta'],
            ['slug' => 'whatsapp.orders.confirm', 'name' => 'confirmar borrador de pedido del bot',  'description' => 'Puede confirmar un borrador de pedido creado por el agente IA'],
            ['slug' => 'whatsapp.orders.reject',  'name' => 'rechazar pedidos del bot',              'description' => 'Puede rechazar un borrador de pedido creado por el agente IA'],
            // Agentes IA
            ['slug' => 'agents.index',            'name' => 'ver agentes IA',                        'description' => 'Puede ver la lista de agentes de venta IA'],
            ['slug' => 'agents.crud',             'name' => 'crear/editar/eliminar agentes IA',      'description' => 'Puede dar de alta, editar y eliminar agentes de venta IA'],
            ['slug' => 'agents.toggle',           'name' => 'activar/desactivar agentes IA',         'description' => 'Puede activar o desactivar un agente de venta IA'],
            // Finanzas
            ['slug' => 'finanzas.gastos.index',   'name' => 'ver gastos',                            'description' => 'Puede ver el listado de gastos'],
            ['slug' => 'finanzas.gastos.crud',    'name' => 'crear/editar/eliminar gastos',          'description' => 'Puede dar de alta, editar y eliminar gastos'],
            ['slug' => 'finanzas.gastos.pagar',   'name' => 'pagar gastos',                          'description' => 'Puede registrar el pago de un gasto'],
            ['slug' => 'finanzas.gastos.categorias', 'name' => 'gestionar categorías de gastos',     'description' => 'Puede crear, editar y eliminar categorías de gastos'],
            ['slug' => 'finanzas.envios.index',   'name' => 'ver envios y fletes',                   'description' => 'Puede ver el listado de envios'],
            ['slug' => 'finanzas.envios.store',   'name' => 'crear envíos',                          'description' => 'Puede crear un envío/flete'],
            ['slug' => 'finanzas.envios.update',  'name' => 'editar envíos',                         'description' => 'Puede editar un envío/flete'],
            ['slug' => 'finanzas.envios.estado',  'name' => 'cambiar estado de envíos',              'description' => 'Puede avanzar la etapa de un envío'],
            ['slug' => 'finanzas.envios.destroy', 'name' => 'eliminar envíos',                       'description' => 'Puede eliminar un envío'],
            ['slug' => 'finanzas.transportistas.crud', 'name' => 'crear/editar/eliminar transportistas', 'description' => 'Puede crear, editar y eliminar transportistas'],
            ['slug' => 'finanzas.transportistas.rendir', 'name' => 'rendir fleteros',                'description' => 'Puede liquidar la rendición de un fletero'],
            ['slug' => 'finanzas.cxp.index',      'name' => 'ver cuentas por pagar',                 'description' => 'Puede ver la cuenta corriente de proveedores'],
            ['slug' => 'finanzas.cxp.pagar',      'name' => 'pagar a proveedores (CxP)',             'description' => 'Puede registrar pagos a proveedores'],
            ['slug' => 'finanzas.cxp.ajustar',    'name' => 'ajustar cuenta corriente de proveedores', 'description' => 'Puede hacer ajustes manuales en la cuenta corriente de un proveedor'],
            ['slug' => 'finanzas.dashboard',      'name' => 'ver tablero financiero',                'description' => 'Puede ver el tablero de flujo de caja y resultados'],
            ['slug' => 'finanzas.chytapay.conectar', 'name' => 'conectar Chytapay',                  'description' => 'Puede conectar una cuenta a Chytapay'],
            ['slug' => 'finanzas.chytapay.desconectar', 'name' => 'desconectar Chytapay',            'description' => 'Puede desconectar una cuenta de Chytapay'],
            ['slug' => 'finanzas.chytapay.sincronizar', 'name' => 'sincronizar cobros Chytapay',     'description' => 'Puede sincronizar manualmente los cobros de Chytapay'],
            ['slug' => 'finanzas.cheques.index',  'name' => 'ver cartera de cheques',                'description' => 'Puede ver el listado de cheques propios y de terceros'],
            ['slug' => 'finanzas.cheques.depositar', 'name' => 'depositar cheques',                  'description' => 'Puede marcar un cheque de tercero como depositado'],
            ['slug' => 'finanzas.cheques.acreditar', 'name' => 'acreditar cheques',                  'description' => 'Puede marcar un cheque como acreditado'],
            ['slug' => 'finanzas.cheques.rechazar', 'name' => 'rechazar cheques',                    'description' => 'Puede marcar un cheque como rechazado'],
            ['slug' => 'finanzas.cheques.anular', 'name' => 'anular cheques',                        'description' => 'Puede anular un cheque'],
            ['slug' => 'finanzas.divisas.index',  'name' => 'ver compra/venta de divisas',           'description' => 'Puede ver el historial de compra/venta de moneda extranjera'],
            ['slug' => 'finanzas.divisas.manage', 'name' => 'gestionar compra/venta de divisas',     'description' => 'Puede registrar compras y ventas de moneda extranjera'],
            ['slug' => 'finanzas.marketing.index', 'name' => 'ver panel de Meta/Google Ads',         'description' => 'Puede ver el gasto publicitario de Meta Ads y Google Ads'],
            ['slug' => 'finanzas.marketing.sincronizar', 'name' => 'sincronizar Meta/Google Ads',    'description' => 'Puede sincronizar manualmente el gasto de Meta Ads y Google Ads'],
            // Alta rapida y otras acciones puntuales
            ['slug' => 'almacen_articulo.bulk_upload', 'name' => 'importar productos por Excel',     'description' => 'Puede hacer carga masiva de productos desde un Excel'],
            ['slug' => 'almacen_articulo.quick_create', 'name' => 'crear categoría/marca/unidad al vuelo', 'description' => 'Puede crear categorías, marcas o unidades rápido desde el alta de productos'],
            ['slug' => 'compras_proveedor.quick_create', 'name' => 'alta rápida de proveedores',     'description' => 'Puede crear un proveedor rápido desde Compras'],
            ['slug' => 'ventas_cliente.quick_create', 'name' => 'alta rápida de clientes',           'description' => 'Puede crear un cliente rápido desde Ventas'],
            ['slug' => 'ventas.cc.movimiento',    'name' => 'registrar movimientos de cuenta corriente de clientes', 'description' => 'Puede registrar cargos/pagos en la cuenta corriente de un cliente'],
            // Notas recordatorias
            ['slug' => 'notas.index', 'name' => 'usar notas recordatorias',                          'description' => 'Puede ver, crear, tildar y borrar notas recordatorias (generales o pegadas a un cliente/proveedor/venta/compra)'],
            // Inversores
            ['slug' => 'inversores.index',      'name' => 'ver inversores',                          'description' => 'Puede ver la lista de inversores, su saldo y su historial de movimientos'],
            ['slug' => 'inversores.crud',       'name' => 'crear/editar/eliminar inversores',        'description' => 'Puede dar de alta, editar y eliminar inversores'],
            ['slug' => 'inversores.movimiento', 'name' => 'registrar aportes/retiros de inversores', 'description' => 'Puede registrar un aporte o retiro de un inversor'],
            ['slug' => 'inversores.reparto',    'name' => 'repartir ganancias entre inversores',     'description' => 'Puede ejecutar un reparto de ganancias por % de participación entre todos los inversores activos'],
            // Gap preexistente: ConfiguracionController/IntegracionController exigen este slug pero nunca se habia sembrado
            ['slug' => 'configuracion.index', 'name' => 'ver configuración del sistema',            'description' => 'Puede ver la pantalla de Configuración e Integraciones'],
            ['slug' => 'configuracion.integraciones.guardar', 'name' => 'guardar integraciones',     'description' => 'Puede guardar claves de API en Integraciones'],
            // Seguridad y confidencialidad
            ['slug' => 'admin.auditoria.index',   'name' => 'ver auditoría del sistema',             'description' => 'Puede ver el log de acciones de todos los usuarios (quién hizo qué, cuándo)'],
            ['slug' => 'ventas.ver_todas',        'name' => 'ver ventas de todos los vendedores',    'description' => 'Sin este permiso, un vendedor solo ve sus propias ventas'],
            ['slug' => 'productos.ver_costos',    'name' => 'ver costos y márgenes de productos',    'description' => 'Sin este permiso, no se muestran costos ni márgenes en listados e informes'],
            // Gap preexistente: GraphicsController exige este slug pero nunca se habia sembrado
            // (solo los roles full-access podian abrir Informes hasta ahora)
            ['slug' => 'reporte.index',           'name' => 'ver informes del negocio',              'description' => 'Puede ver el tablero de Informes (ventas, productos, clientes, stock, finanzas)'],
            // Solicitudes de aprobacion (anulaciones, compra/venta de divisas)
            ['slug' => 'admin.solicitudes.index', 'name' => 'ver solicitudes de aprobación',         'description' => 'Puede ver la lista de solicitudes pendientes de aprobación'],
            ['slug' => 'admin.solicitudes.aprobar', 'name' => 'aprobar solicitudes',                 'description' => 'Puede aprobar solicitudes pendientes y ejecutar la acción'],
            ['slug' => 'admin.solicitudes.rechazar', 'name' => 'rechazar solicitudes',                'description' => 'Puede rechazar solicitudes pendientes'],
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
