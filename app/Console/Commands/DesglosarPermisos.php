<?php

namespace App\Console\Commands;

use App\Permission\Models\Permission;
use App\Permission\Models\Role;
use App\Permission\Models\UserPermission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Desglosa permisos "paquete" (una accion.manage que hoy tapa varias
 * acciones distintas) en permisos puntuales, sin que nadie pierda acceso:
 * todo Role/override por persona que tenia el permiso viejo recibe TODOS
 * los nuevos equivalentes. Se corre una sola vez; se puede volver a correr
 * sin problema (idempotente).
 */
class DesglosarPermisos extends Command
{
    protected $signature = 'permisos:desglosar';

    protected $description = 'Desglosa permisos paquete en permisos puntuales (ver plan de "permisos mas especificos")';

    /**
     * slug viejo => [
     *   'borrar_viejo' => si se elimina el permiso viejo despues de migrar (false = era mixto, sigue vivo para la parte de lectura),
     *   'nuevos' => [slug => [name, description], ...],
     * ]
     */
    private function mapa(): array
    {
        return [
            'almacen_articulo.index' => ['borrar_viejo' => false, 'nuevos' => [
                'almacen_articulo.bulk_upload' => ['importar productos por Excel', 'Puede hacer carga masiva de productos desde un Excel'],
                'almacen_articulo.quick_create' => ['crear categoría/marca/unidad al vuelo', 'Puede crear categorías, marcas o unidades rápido desde el alta de productos'],
            ]],
            'admin.solicitudes.manage' => ['borrar_viejo' => true, 'nuevos' => [
                'admin.solicitudes.aprobar' => ['aprobar solicitudes', 'Puede aprobar solicitudes pendientes y ejecutar la acción'],
                'admin.solicitudes.rechazar' => ['rechazar solicitudes', 'Puede rechazar solicitudes pendientes'],
            ]],
            'finanzas.chytapay.manage' => ['borrar_viejo' => true, 'nuevos' => [
                'finanzas.chytapay.conectar' => ['conectar Chytapay', 'Puede conectar una cuenta a Chytapay'],
                'finanzas.chytapay.desconectar' => ['desconectar Chytapay', 'Puede desconectar una cuenta de Chytapay'],
                'finanzas.chytapay.sincronizar' => ['sincronizar cobros Chytapay', 'Puede sincronizar manualmente los cobros de Chytapay'],
            ]],
            'finanzas.cheques.manage' => ['borrar_viejo' => true, 'nuevos' => [
                'finanzas.cheques.depositar' => ['depositar cheques', 'Puede marcar un cheque de tercero como depositado'],
                'finanzas.cheques.acreditar' => ['acreditar cheques', 'Puede marcar un cheque como acreditado'],
                'finanzas.cheques.rechazar' => ['rechazar cheques', 'Puede marcar un cheque como rechazado'],
                'finanzas.cheques.anular' => ['anular cheques', 'Puede anular un cheque'],
            ]],
            'finanzas.envios.manage' => ['borrar_viejo' => true, 'nuevos' => [
                'finanzas.envios.store' => ['crear envíos', 'Puede crear un envío/flete'],
                'finanzas.envios.update' => ['editar envíos', 'Puede editar un envío/flete'],
                'finanzas.envios.estado' => ['cambiar estado de envíos', 'Puede avanzar la etapa de un envío'],
                'finanzas.envios.destroy' => ['eliminar envíos', 'Puede eliminar un envío'],
            ]],
            'finanzas.gastos.manage' => ['borrar_viejo' => true, 'nuevos' => [
                'finanzas.gastos.crud' => ['crear/editar/eliminar gastos', 'Puede dar de alta, editar y eliminar gastos'],
                'finanzas.gastos.pagar' => ['pagar gastos', 'Puede registrar el pago de un gasto'],
                'finanzas.gastos.categorias' => ['gestionar categorías de gastos', 'Puede crear, editar y eliminar categorías de gastos'],
            ]],
            'finanzas.transportistas.manage' => ['borrar_viejo' => true, 'nuevos' => [
                'finanzas.transportistas.crud' => ['crear/editar/eliminar transportistas', 'Puede crear, editar y eliminar transportistas'],
                'finanzas.transportistas.rendir' => ['rendir fleteros', 'Puede liquidar la rendición de un fletero'],
            ]],
            'compras.reposicion.manage' => ['borrar_viejo' => true, 'nuevos' => [
                'compras.reposicion.generar' => ['generar reposición', 'Puede generar sugerencias de compra ahora'],
                'compras.reposicion.ajustar' => ['ajustar reposición', 'Puede guardar ajustes/umbrales de reposición'],
            ]],
            'reportes.chat.index' => ['borrar_viejo' => false, 'nuevos' => [
                'reportes.chat.usar' => ['usar el chat de reportes IA', 'Puede crear sesiones y enviar mensajes al analista IA'],
            ]],
            'finanzas.cxp.manage' => ['borrar_viejo' => true, 'nuevos' => [
                'finanzas.cxp.pagar' => ['pagar a proveedores (CxP)', 'Puede registrar pagos a proveedores'],
                'finanzas.cxp.ajustar' => ['ajustar cuenta corriente de proveedores', 'Puede hacer ajustes manuales en la cuenta corriente de un proveedor'],
            ]],
            'finanzas.cobranzas.send' => ['borrar_viejo' => true, 'nuevos' => [
                'finanzas.cobranzas.aprobar' => ['aprobar cobranza pendiente', 'Puede aprobar y enviar un recordatorio de cobranza'],
                'finanzas.cobranzas.descartar' => ['descartar cobranzas', 'Puede descartar un recordatorio de cobranza pendiente'],
            ]],
            'agents.manage' => ['borrar_viejo' => true, 'nuevos' => [
                'agents.crud' => ['crear/editar/eliminar agentes IA', 'Puede dar de alta, editar y eliminar agentes de venta IA'],
                'agents.toggle' => ['activar/desactivar agentes IA', 'Puede activar o desactivar un agente de venta IA'],
            ]],
            'whatsapp.index' => ['borrar_viejo' => false, 'nuevos' => [
                'whatsapp.note' => ['agregar notas internas', 'Puede agregar notas internas a una conversación'],
                'whatsapp.set_status' => ['cambiar estado de conversación', 'Puede cambiar el estado de una conversación de WhatsApp'],
                'whatsapp.toggle_mode' => ['pasar conversación a humano/bot', 'Puede tomar o devolver el control de una conversación al bot'],
                'whatsapp.link_cliente' => ['vincular cliente a conversación', 'Puede vincular o crear un cliente desde una conversación'],
                'whatsapp.tag' => ['etiquetar conversaciones', 'Puede poner/quitar etiquetas a una conversación'],
                'whatsapp.tags.store' => ['crear etiquetas', 'Puede crear una etiqueta nueva'],
            ]],
            'whatsapp.assign' => ['borrar_viejo' => false, 'nuevos' => [
                'whatsapp.tags.destroy' => ['eliminar etiquetas', 'Puede eliminar una etiqueta'],
            ]],
            'whatsapp.confirm_order' => ['borrar_viejo' => true, 'nuevos' => [
                'whatsapp.orders.confirm' => ['confirmar borrador de pedido del bot', 'Puede confirmar un borrador de pedido creado por el agente IA'],
                'whatsapp.orders.reject' => ['rechazar pedidos del bot', 'Puede rechazar un borrador de pedido creado por el agente IA'],
            ]],
            'configuracion.index' => ['borrar_viejo' => false, 'nuevos' => [
                'configuracion.integraciones.guardar' => ['guardar integraciones', 'Puede guardar claves de API en Integraciones'],
            ]],
            'ventas.index' => ['borrar_viejo' => false, 'nuevos' => [
                'ventas.cc.movimiento' => ['registrar movimientos de cuenta corriente de clientes', 'Puede registrar cargos/pagos en la cuenta corriente de un cliente'],
            ]],
            'finanzas.marketing.index' => ['borrar_viejo' => false, 'nuevos' => [
                'finanzas.marketing.sincronizar' => ['sincronizar Meta/Google Ads', 'Puede sincronizar manualmente el gasto de Meta Ads y Google Ads'],
            ]],
            'compras_proveedor.index' => ['borrar_viejo' => false, 'nuevos' => [
                'compras_proveedor.quick_create' => ['alta rápida de proveedores', 'Puede crear un proveedor rápido desde Compras'],
            ]],
            'ventas_cliente.index' => ['borrar_viejo' => false, 'nuevos' => [
                'ventas_cliente.quick_create' => ['alta rápida de clientes', 'Puede crear un cliente rápido desde Ventas'],
            ]],
        ];
    }

    public function handle(): int
    {
        $totalNuevos = 0;
        $totalRolesMigrados = 0;
        $totalOverridesMigrados = 0;

        DB::transaction(function () use (&$totalNuevos, &$totalRolesMigrados, &$totalOverridesMigrados) {
            foreach ($this->mapa() as $slugViejo => $config) {
                $viejo = Permission::where('slug', $slugViejo)->first();

                $nuevosIds = [];
                foreach ($config['nuevos'] as $slugNuevo => [$name, $description]) {
                    $permiso = Permission::firstOrCreate(
                        ['slug' => $slugNuevo],
                        ['name' => $name, 'description' => $description]
                    );
                    $nuevosIds[] = $permiso->id;
                    $totalNuevos++;
                }

                if (!$viejo) {
                    // El slug viejo no existe en esta base (ej. instalacion nueva) — no hay nada que migrar
                    continue;
                }

                foreach ($viejo->roles as $role) {
                    $role->permissions()->syncWithoutDetaching($nuevosIds);
                    $totalRolesMigrados++;
                }

                $overridesViejos = UserPermission::where('permission_id', $viejo->id)->get();
                foreach ($overridesViejos as $override) {
                    foreach ($nuevosIds as $nuevoId) {
                        UserPermission::firstOrCreate(
                            ['user_id' => $override->user_id, 'permission_id' => $nuevoId],
                            ['tipo' => $override->tipo]
                        );
                    }
                    $totalOverridesMigrados++;
                }

                if ($config['borrar_viejo']) {
                    $viejo->delete();
                }
            }
        });

        $this->info("Permisos nuevos creados/existentes: {$totalNuevos}");
        $this->info("Roles migrados: {$totalRolesMigrados}");
        $this->info("Overrides por persona migrados: {$totalOverridesMigrados}");
        $this->info('Total de permisos en el sistema ahora: ' . Permission::count());

        return self::SUCCESS;
    }
}
