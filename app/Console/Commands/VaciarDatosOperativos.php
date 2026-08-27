<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Vacía TODOS los datos operativos/transaccionales (ventas, compras, presupuestos,
 * gastos, cheques, movimientos, envíos, WhatsApp/CRM, auditoría, revendedores,
 * Chytapay, publicaciones IA, pedidos ecommerce, notificaciones, aportes/retiros
 * de inversores) para arrancar las métricas de 0, sin tocar productos, proveedores
 * ni clientes/usuarios/roles.
 *
 * Reinicia también el stock de productos a 0 (incluido el stock por sucursal).
 * No toca notas recordatorias ni la lista de inversores (son datos de
 * planificación/maestro, no métricas del período).
 *
 * Se corre UNA sola vez y se borra después de usarla. NO tiene --force: siempre
 * pide escribir la frase de confirmación, para evitar un uso accidental.
 */
class VaciarDatosOperativos extends Command
{
    protected $signature = 'sistema:vaciar-datos-operativos';

    protected $description = 'PELIGRO: borra todos los datos operativos (ventas, compras, presupuestos, etc.) dejando productos/proveedores/clientes/usuarios intactos';

    /** Tablas que se truncan por completo (reinician también el auto_increment). */
    private array $tablasATruncar = [
        // Ventas / facturación
        'ventas', 'detalle_ventas', 'venta_pago_comprobantes',
        'compras', 'detalle_compras', 'compra_adjuntos', 'compra_ocr_extracciones',
        'pedidos_compra', 'detalle_pedidos_compra',
        'presupuestos', 'detalles_presupuesto',
        'cotizaciones', 'detalle_cotizacion', 'carrito_cotizacion_temp',
        'ingresos', 'detalle_ingresos',
        'devoluciones', 'devolucion_ventas', 'detalle_devolucion_ventas', 'arrepentimientos',
        'detalle_entrada_temp', 'inventario', 'capturarinventario',

        // Financiero
        'movimientos', 'movimientos_bancarios_importados', 'movimientos_stock',
        'gastos',
        'cheques',
        'operaciones_cambio', 'operacion_cambio_consumos',
        'proveedor_cc_movimientos', 'cliente_cc_movimientos',
        'cobranza_recordatorios',
        'solicitudes_aprobacion',
        'aperturacajas', 'aperturacajavirtual', 'caja_aperturas', 'corte_cajero_dia', 'numero_corte_por_cajero',

        // Inversores: aportes/retiros/reparto (se conserva la lista de inversores en sí)
        'inversor_movimientos',

        // Envíos
        'envios', 'entregas_fletero',

        // Marketing
        'ad_spend_diario',

        // Reportes / agentes IA (historial de ejecución, no la config)
        'reportes_chat_sesiones', 'reportes_chat_mensajes',
        'ai_agent_runs',

        // WhatsApp / CRM (historial de conversaciones)
        'wa_conversations', 'wa_conversation_tag', 'wa_messages', 'wa_order_drafts',

        // Auditoría
        'audit_logs',

        // Revendedores
        'revendedores', 'revendedor_comisiones', 'revendedor_pagos',

        // Chytapay (conexión OAuth, hay que re-vincular después)
        'chytapay_conexiones',

        // Publicaciones IA generadas (no toca publicaciones_ajustes, que es config)
        'publicaciones', 'publicaciones_recursos', 'publicaciones_registro',

        // Pedidos del ecommerce
        'order_ecommerce', 'order_detail_ecommerce', 'order_pago_comprobantes',
        'order_stock_asignaciones', 'payment_ecommerce',

        // Notificaciones
        'notificaciones',

        // Colas
        'jobs', 'failed_jobs',
    ];

    public function handle(): int
    {
        $this->warn('¡ATENCIÓN! Esto borra TODOS los datos operativos de la base de datos:');
        $this->line('  - Ventas, compras, pedidos de compra, presupuestos, cotizaciones, devoluciones');
        $this->line('  - Gastos, cheques, movimientos de caja/banco, compra/venta de divisas');
        $this->line('  - Cuentas corrientes de clientes y proveedores, cobranzas, solicitudes de aprobación');
        $this->line('  - Envíos, gasto de Meta/Google Ads, notificaciones, cola de jobs');
        $this->line('  - Historial de WhatsApp/CRM, logs de auditoría, revendedores y sus pagos/comisiones');
        $this->line('  - Conexión a Chytapay, publicaciones IA generadas, pedidos del ecommerce');
        $this->line('  - Aportes/retiros/reparto de inversores (se mantiene la lista de inversores)');
        $this->line('  - El stock de TODOS los productos (general y por sucursal) vuelve a 0');
        $this->newLine();
        $this->info('Se conservan intactos: productos/catálogo, proveedores, clientes, usuarios/roles/permisos, sucursales, cajas/bancos, config del sistema, notas recordatorias, lista de inversores.');
        $this->newLine();

        $respuesta = $this->ask('Para confirmar, escribí exactamente: BORRAR TODO');
        if ($respuesta !== 'BORRAR TODO') {
            $this->error('Confirmación incorrecta. Operación cancelada, no se tocó nada.');
            return self::FAILURE;
        }

        $totalTruncadas = 0;
        $noEncontradas = [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->tablasATruncar as $tabla) {
                if (Schema::hasTable($tabla)) {
                    DB::table($tabla)->truncate();
                    $totalTruncadas++;
                } else {
                    $noEncontradas[] = $tabla;
                }
            }

            if (Schema::hasTable('productos') && Schema::hasColumn('productos', 'stock')) {
                DB::table('productos')->update(['stock' => 0]);
            }
            if (Schema::hasTable('sucursal_articulo')) {
                DB::table('sucursal_articulo')->update(['stock' => 0]);
            }
            if (Schema::hasTable('sucursal_combinacion')) {
                DB::table('sucursal_combinacion')->update(['stock' => 0]);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        Log::warning('sistema:vaciar-datos-operativos ejecutado', [
            'tablas_truncadas' => $totalTruncadas,
            'tablas_no_encontradas' => $noEncontradas,
            'fecha' => now()->toDateTimeString(),
        ]);

        $this->info("Listo. {$totalTruncadas} tablas vaciadas, stock de productos reiniciado a 0.");
        if ($noEncontradas) {
            $this->comment('No existían (se ignoraron): ' . implode(', ', $noEncontradas));
        }

        return self::SUCCESS;
    }
}
