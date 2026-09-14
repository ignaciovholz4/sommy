<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\CompraAdjunto;
use App\Models\Devolucion;
use App\Models\Gasto;
use App\Models\Movimiento;
use App\Models\OperacionCambio;
use App\Models\Venta;
use App\Models\ecommerce\order_ecommerce;
use App\Services\Ai\ReportesTools\CuentasPorPagarQueryTool;
use App\Services\Ai\ReportesTools\DeudoresQueryTool;
use App\Services\Ai\ReportesTools\TesoreriaQueryTool;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Todos los movimientos de caja/banco de hoy o del mes, en un solo lugar,
 * para que el cierre del dia sea "entrar y ver que todo esta en orden".
 */
class ResumenController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->query('periodo', 'hoy') === 'mes' ? 'mes' : 'hoy';

        $desde = $periodo === 'mes' ? now()->startOfMonth() : now()->startOfDay();
        $hasta = now()->endOfDay();

        $movimientos = Movimiento::with('cuenta.moneda')
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderByDesc('fecha')
            ->get();

        // Los movimientos sin cuenta (ej. pago con un cheque de cartera
        // endosado) se agrupan como ARS por defecto: no representan actividad
        // de una cuenta en otra moneda.
        $codigoMoneda = fn (Movimiento $m) => optional(optional($m->cuenta)->moneda)->codigo ?? 'ARS';
        $simboloMoneda = fn (Movimiento $m) => optional(optional($m->cuenta)->moneda)->simbolo ?? '$';

        $totalesPorMoneda = $movimientos->groupBy($codigoMoneda)->map(function ($grupo, $codigo) use ($simboloMoneda) {
            $totales = [
                'moneda'    => $codigo,
                'simbolo'   => $simboloMoneda($grupo->first()),
                'ingresos'  => (float) $grupo->where('tipo', 'ingreso')->sum('total'),
                'egresos'   => (float) $grupo->where('tipo', 'egreso')->sum('total'),
                'efectivo'  => (float) $grupo->sum('efectivo'),
                'bancos'    => (float) $grupo->sum('bancos'),
                'tarjetas'  => (float) $grupo->sum('tarjetas'),
                'cheques'   => (float) $grupo->sum('cheques'),
            ];
            $totales['neto'] = $totales['ingresos'] - $totales['egresos'];

            return $totales;
        })->sortByDesc(fn ($t) => $t['moneda'] === 'ARS' ? 1 : 0)->values();

        // Totales "principales" (ARS) para los KPI grandes de arriba, con fallback vacío
        $totales = $totalesPorMoneda->firstWhere('moneda', 'ARS') ?? [
            'moneda' => 'ARS', 'simbolo' => '$', 'ingresos' => 0, 'egresos' => 0,
            'efectivo' => 0, 'bancos' => 0, 'tarjetas' => 0, 'cheques' => 0, 'neto' => 0,
        ];

        $actividad = $this->actividadDelPeriodo($desde, $hasta);
        $comprobantes = $this->comprobantesDelPeriodo($desde, $hasta, $movimientos);

        $fleterosEfectivo = DB::table('entregas_fletero as ef')
            ->join('transportistas as t', 't.id', '=', 'ef.transportista_id')
            ->where('ef.rendido', false)
            ->groupBy('t.id', 't.nombre')
            ->selectRaw('t.id, t.nombre, SUM(ef.monto_cobrado) as pendiente')
            ->havingRaw('SUM(ef.monto_cobrado) > 0')
            ->get();

        $historicoMensual = $this->historicoMensual('ARS');
        $historicoTotal = [
            'ingresos'           => (float) $historicoMensual->sum('ingresos'),
            'egresos_stock'      => (float) $historicoMensual->sum('egresos_stock'),
            'egresos_operativos' => (float) $historicoMensual->sum('egresos_operativos'),
            'neto'               => (float) $historicoMensual->sum('neto'),
        ];

        // Monedas extranjeras con actividad alguna vez (no solo en este período),
        // para saber si hace falta mostrar su propia sección de histórico/saldo.
        $monedasExtranjeras = DB::table('movimientos as m')
            ->join('cuentas as c', 'c.id', '=', 'm.cuenta_id')
            ->join('monedas as mo', 'mo.id', '=', 'c.moneda_id')
            ->where('mo.codigo', '!=', 'ARS')
            ->distinct()
            ->pluck('mo.codigo', 'mo.codigo');

        $historicoPorMoneda = $monedasExtranjeras->mapWithKeys(function ($codigo) {
            $mensual = $this->historicoMensual($codigo);

            return [$codigo => [
                'mensual' => $mensual,
                'total' => [
                    'ingresos'           => (float) $mensual->sum('ingresos'),
                    'egresos_stock'      => (float) $mensual->sum('egresos_stock'),
                    'egresos_operativos' => (float) $mensual->sum('egresos_operativos'),
                    'neto'               => (float) $mensual->sum('neto'),
                ],
            ]];
        });

        // Foto actual (no del período): cuánto hay en caja/banco, cuánto me deben
        // los clientes y cuánto le debo a proveedores. Reusa las mismas consultas
        // que usa el chat de Reportes para que los números coincidan siempre.
        // OJO: saldo_total_actual de la tesorería suma cuentas de TODAS las
        // monedas como si fueran la misma unidad — para "Caja + bancos ahora"
        // usamos $saldoPorMoneda (abajo), que sí las separa.
        $tesoreria = (new TesoreriaQueryTool())->execute([]);
        $porCobrar = (new DeudoresQueryTool())->execute(['limit' => 10]);
        $porPagar = (new CuentasPorPagarQueryTool())->execute(['limit' => 10]);

        $saldoPorMoneda = $this->saldoPorMoneda();
        $saldoArs = $saldoPorMoneda->get('ARS')['saldo'] ?? 0.0;
        $posicionNeta = $saldoArs + $porCobrar['deuda_total'] - $porPagar['deuda_total'];

        // Cómo se transformaron los dólares (y otras monedas) en pesos y viceversa:
        // cada compra/venta de moneda extranjera, con la cotización usada y el
        // resultado (ganancia/pérdida cambiaria) cuando corresponde.
        $operacionesCambio = OperacionCambio::with('moneda')
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        return view('finanzas.resumen.index', [
            'periodo'            => $periodo,
            'desde'              => $desde,
            'hasta'              => $hasta,
            'movimientos'        => $movimientos,
            'totales'            => $totales,
            'totalesPorMoneda'   => $totalesPorMoneda,
            'fleterosEfectivo'   => $fleterosEfectivo,
            'actividad'          => $actividad,
            'comprobantes'       => $comprobantes,
            'historicoMensual'   => $historicoMensual,
            'historicoTotal'     => $historicoTotal,
            'historicoPorMoneda' => $historicoPorMoneda,
            'tesoreria'          => $tesoreria,
            'porCobrar'          => $porCobrar,
            'porPagar'           => $porPagar,
            'saldoPorMoneda'     => $saldoPorMoneda,
            'saldoArs'           => $saldoArs,
            'posicionNeta'       => $posicionNeta,
            'operacionesCambio'  => $operacionesCambio,
        ]);
    }

    /**
     * Saldo actual de cada cuenta sumado por moneda (no mezcla ARS con USD:
     * son unidades distintas). Mismo criterio de "sin moneda = ARS" que el
     * resto de la página.
     */
    private function saldoPorMoneda()
    {
        return DB::table('cuentas as cu')
            ->leftJoin('monedas as mo', 'mo.id', '=', 'cu.moneda_id')
            ->leftJoin('movimientos as m', 'm.cuenta_id', '=', 'cu.id')
            ->where('cu.activa', 1)
            ->groupBy('moneda', 'simbolo')
            ->selectRaw("COALESCE(mo.codigo, 'ARS') as moneda, COALESCE(mo.simbolo, '$') as simbolo,
                COALESCE(SUM(CASE WHEN m.tipo = 'ingreso' THEN m.total WHEN m.tipo = 'egreso' THEN -m.total ELSE 0 END), 0) as saldo")
            ->get()
            ->map(fn ($r) => ['moneda' => $r->moneda, 'simbolo' => $r->simbolo, 'saldo' => (float) $r->saldo])
            ->keyBy('moneda');
    }

    /**
     * Ganancia/pérdida mes a mes en una moneda dada (por defecto ARS: cuentas
     * sin moneda cargada se consideran ARS, igual criterio que $totalesPorMoneda
     * arriba) desde el primer movimiento cargado hasta hoy, con acumulado histórico.
     *
     * Comprar mercadería no es una pérdida: esa plata se convirtió en stock
     * (un activo), no se esfumó. Por eso separamos los egresos en dos:
     * "egresos_stock" (pagos de compras, directos o cancelando cuenta
     * corriente de proveedor) y "egresos_operativos" (gastos reales:
     * alquiler, sueldos, marketing, etc.). Solo estos últimos restan en el
     * resultado del mes — las compras se muestran aparte, como inversión.
     */
    private function historicoMensual(string $monedaCodigo = 'ARS')
    {
        $filas = DB::table('movimientos as m')
            ->leftJoin('cuentas as c', 'c.id', '=', 'm.cuenta_id')
            ->leftJoin('monedas as mo', 'mo.id', '=', 'c.moneda_id')
            // Pago directo de una compra: el comprobante del movimiento es el num_folio de la compra.
            ->leftJoin('compras as cmp', function ($j) {
                $j->on('cmp.num_folio', '=', 'm.comprobante')->whereNotNull('m.comprobante');
            })
            // Pago que cancela deuda de cuenta corriente con un proveedor.
            ->leftJoin('proveedor_cc_movimientos as ccm', function ($j) {
                $j->on('ccm.id', '=', 'm.referencia_id')->where('m.referencia_type', '=', \App\Models\ProveedorCcMovimiento::class);
            })
            ->where(function ($q) use ($monedaCodigo) {
                if ($monedaCodigo === 'ARS') {
                    $q->whereNull('mo.codigo')->orWhere('mo.codigo', 'ARS');
                } else {
                    $q->where('mo.codigo', $monedaCodigo);
                }
            })
            ->selectRaw("DATE_FORMAT(m.fecha, '%Y-%m-01') as mes,
                SUM(CASE WHEN m.tipo = 'ingreso' THEN m.total ELSE 0 END) as ingresos,
                SUM(CASE WHEN m.tipo = 'egreso' AND (cmp.idcompra IS NOT NULL OR ccm.compra_id IS NOT NULL) THEN m.total ELSE 0 END) as egresos_stock,
                SUM(CASE WHEN m.tipo = 'egreso' AND cmp.idcompra IS NULL AND ccm.compra_id IS NULL THEN m.total ELSE 0 END) as egresos_operativos")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $acumulado = 0;

        return $filas->map(function ($f) use (&$acumulado) {
            $ingresos = (float) $f->ingresos;
            $egresosStock = (float) $f->egresos_stock;
            $egresosOperativos = (float) $f->egresos_operativos;
            $neto = $ingresos - $egresosOperativos;
            $acumulado += $neto;

            return [
                'fecha'             => Carbon::parse($f->mes),
                'ingresos'          => $ingresos,
                'egresos_stock'     => $egresosStock,
                'egresos_operativos'=> $egresosOperativos,
                'neto'              => $neto,
                'acumulado'         => $acumulado,
            ];
        });
    }

    /**
     * Comprobantes subidos en el período: adjuntos de compras, de pagos de
     * ventas manuales y de movimientos de caja/banco (ej. pago de un pedido
     * del ecommerce). Sirve para el resumen imprimible del día/mes.
     */
    private function comprobantesDelPeriodo(Carbon $desde, Carbon $hasta, $movimientos)
    {
        $deCompras = CompraAdjunto::with('compra')
            ->whereBetween('created_at', [$desde, $hasta])
            ->get()
            ->map(fn (CompraAdjunto $a) => [
                'tipo'      => 'Compra',
                'titulo'    => 'Compra #' . (optional($a->compra)->num_folio ?? $a->compra_id),
                'archivo'   => $a->original_name,
                'url'       => $a->url,
                'es_imagen' => $a->es_imagen,
                'fecha'     => $a->created_at,
                'link'      => route('compras.index'),
            ]);

        $deVentas = DB::table('venta_pago_comprobantes as vc')
            ->leftJoin('ventas as v', 'v.idventa', '=', 'vc.venta_id')
            ->whereBetween('vc.created_at', [$desde, $hasta])
            ->select('vc.archivo', 'vc.nota', 'vc.mime', 'vc.created_at', 'vc.venta_id', 'v.num_folio')
            ->get()
            ->map(fn ($c) => [
                'tipo'      => 'Venta',
                'titulo'    => 'Venta #' . ($c->num_folio ?? $c->venta_id),
                'archivo'   => $c->nota ?: basename($c->archivo),
                'url'       => asset($c->archivo),
                'es_imagen' => str_starts_with((string) $c->mime, 'image/'),
                'fecha'     => Carbon::parse($c->created_at),
                'link'      => route('ventas.index'),
            ]);

        $deMovimientos = $movimientos
            ->filter(fn (Movimiento $m) => !empty($m->adjunto_path))
            ->map(fn (Movimiento $m) => [
                'tipo'      => 'Movimiento',
                'titulo'    => ($m->tipo === 'ingreso' ? 'Ingreso' : 'Egreso') . ' — ' . (optional($m->cuenta)->nombre ?? '—'),
                'archivo'   => $m->adjunto_nombre ?: basename($m->adjunto_path),
                'url'       => \Illuminate\Support\Facades\Storage::disk('public')->url($m->adjunto_path),
                'es_imagen' => in_array(strtolower(pathinfo($m->adjunto_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']),
                'fecha'     => $m->fecha,
                'link'      => url('finanzas/resumen'),
            ]);

        return $deCompras->concat($deVentas)->concat($deMovimientos)
            ->sortByDesc('fecha')
            ->values();
    }

    /**
     * Todo lo que se fue cargando en el período (compras, ventas, pedidos,
     * gastos, devoluciones), tenga o no movimiento de caja asociado.
     * Distinto de $movimientos: acá entra un documento apenas se crea,
     * aunque siga "a pagar"/pendiente.
     */
    private function actividadDelPeriodo(Carbon $desde, Carbon $hasta)
    {
        $nombreCliente = fn ($cliente) => $cliente
            ? trim(collect([$cliente->nombre, $cliente->paterno, $cliente->materno])->filter()->implode(' '))
            : '—';

        $compras = Compra::with('proveedor')
            ->whereBetween('created_at', [$desde, $hasta])
            ->get()
            ->map(fn (Compra $c) => [
                'tipo'         => 'Compra',
                'icono'        => 'fa-truck-loading',
                'titulo'       => 'Compra #' . $c->num_folio,
                'subtitulo'    => optional($c->proveedor)->nombre ?? '—',
                'monto'        => (float) $c->total_con_iva,
                'estado'       => ucfirst($c->estado),
                'estadoColor'  => $c->estado === 'anulada' ? 'danger' : ($c->estado === 'pagada' ? 'success' : 'warning'),
                'fecha'        => $c->created_at,
                'link'         => route('compras.index'),
            ]);

        $ventas = Venta::with('cliente')
            ->whereBetween('created_at', [$desde, $hasta])
            ->get()
            ->map(fn (Venta $v) => [
                'tipo'         => 'Venta',
                'icono'        => 'fa-cash-register',
                'titulo'       => 'Venta #' . $v->num_folio,
                'subtitulo'    => $nombreCliente($v->cliente),
                'monto'        => (float) $v->total_con_iva,
                'estado'       => ucfirst($v->estado),
                'estadoColor'  => $v->estado === 'anulada' ? 'danger' : 'success',
                'fecha'        => $v->created_at,
                'link'         => route('ventas.index'),
            ]);

        $pedidos = order_ecommerce::with(['cliente', 'status'])
            ->whereBetween('order_date', [$desde, $hasta])
            ->get()
            ->map(fn (order_ecommerce $o) => [
                'tipo'         => 'Pedido',
                'icono'        => 'fa-shopping-basket',
                'titulo'       => 'Pedido #' . $o->order_id,
                'subtitulo'    => $nombreCliente($o->cliente),
                'monto'        => (float) $o->total_amount,
                'estado'       => optional($o->status)->status_name ?? '—',
                'estadoColor'  => $o->status_order_id == 6 ? 'danger' : 'success',
                'fecha'        => Carbon::parse($o->order_date),
                'link'         => route('order.edit', $o->order_id),
            ]);

        $gastos = Gasto::with('proveedor')
            ->whereBetween('created_at', [$desde, $hasta])
            ->get()
            ->map(fn (Gasto $g) => [
                'tipo'         => 'Gasto',
                'icono'        => 'fa-file-invoice-dollar',
                'titulo'       => $g->descripcion ?: ('Gasto #' . $g->id),
                'subtitulo'    => optional($g->proveedor)->nombre ?? '—',
                'monto'        => (float) $g->monto,
                'estado'       => ucfirst($g->estado),
                'estadoColor'  => $g->estado === 'pagado' ? 'success' : 'warning',
                'fecha'        => $g->created_at,
                'link'         => route('finanzas.gastos.index'),
            ]);

        $devoluciones = Devolucion::whereBetween('created_at', [$desde, $hasta])
            ->get()
            ->map(fn (Devolucion $d) => [
                'tipo'         => 'Devolución',
                'icono'        => 'fa-undo',
                'titulo'       => 'Devolución #' . $d->id . ' (' . ucfirst($d->tipo) . ')',
                'subtitulo'    => $d->motivo ?: '—',
                'monto'        => (float) $d->monto,
                'estado'       => '—',
                'estadoColor'  => 'secondary',
                'fecha'        => $d->created_at,
                'link'         => route('devoluciones.index'),
            ]);

        return $compras->concat($ventas)->concat($pedidos)->concat($gastos)->concat($devoluciones)
            ->sortByDesc('fecha')
            ->values();
    }
}
