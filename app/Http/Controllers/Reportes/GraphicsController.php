<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\DB;
use Response;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class GraphicsController extends Controller
{
    /**
     * Tablero de informes con pestañas y filtro por período (desde/hasta).
     */
    public function index(Request $request)
    {
        Gate::authorize('haveaccess', 'reporte.index');

        // ── Período seleccionado (por defecto: mes actual) ───────────
        $desde = $request->filled('desde') ? Carbon::parse($request->input('desde'))->startOfDay() : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->input('hasta'))->endOfDay()   : Carbon::now()->endOfDay();
        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }
        $dias = $desde->diffInDays($hasta) + 1;
        $desdeAnt = $desde->copy()->subDays($dias);
        $hastaAnt = $desde->copy()->subSecond();

        // Aislamiento por sucursal: null = sin restriccion (ve todas). No aplica a pedidos
        // web (order_ecommerce no tiene sucursal_id, la tienda online es unica para todo el negocio).
        $sucursalesPermitidas = auth()->user()->sucursalesPermitidas();

        $ventasValidas = fn () => DB::table('ventas')
            ->where('estado', 'NOT LIKE', 'Cancel%')
            ->where('estado', 'NOT LIKE', 'Anul%')
            ->when($sucursalesPermitidas, fn ($q, $s) => $q->whereIn('sucursal_id', $s));

        $pedidosWebQuery = fn () => DB::table('order_ecommerce')->where('active', 1)->where('status_order_id', '!=', 6);

        // ── KPIs del período vs período anterior ─────────────────────
        $fact    = (float) $ventasValidas()->whereBetween('fecha', [$desde, $hasta])->sum('total_con_iva');
        $factAnt = (float) $ventasValidas()->whereBetween('fecha', [$desdeAnt, $hastaAnt])->sum('total_con_iva');
        $nVentas    = (int) $ventasValidas()->whereBetween('fecha', [$desde, $hasta])->count();
        $nVentasAnt = (int) $ventasValidas()->whereBetween('fecha', [$desdeAnt, $hastaAnt])->count();
        $ticket    = $nVentas    ? $fact    / $nVentas    : 0;
        $ticketAnt = $nVentasAnt ? $factAnt / $nVentasAnt : 0;

        $pedidosWeb    = (int) $pedidosWebQuery()->whereBetween('order_date', [$desde, $hasta])->count();
        $pedidosWebAnt = (int) $pedidosWebQuery()->whereBetween('order_date', [$desdeAnt, $hastaAnt])->count();
        $pedidosWebPendientes = (int) DB::table('order_ecommerce')->where('active', 1)->where('status_order_id', 1)->count();

        $margenRow = DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.idventa', '=', 'dv.venta_id')
            ->join('productos as p', 'p.idarticulo', '=', 'dv.articulo_id')
            ->where('v.estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('v.fecha', [$desde, $hasta])
            ->when($sucursalesPermitidas, fn ($q, $s) => $q->whereIn('v.sucursal_id', $s))
            ->selectRaw('COALESCE(SUM(dv.subtotal_con_iva),0) as venta, COALESCE(SUM(dv.cantidad * p.pcompra_con_iva),0) as costo')
            ->first();
        $margen    = (float) $margenRow->venta - (float) $margenRow->costo;
        $margenPct = $margenRow->venta > 0 ? ($margen / $margenRow->venta) * 100 : 0;
        // Costo/margen quedan ocultos sin permiso explicito (ver mas abajo, antes del return)

        $compras = (float) DB::table('compras')
            ->where('estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('total_con_iva');

        $delta = function ($actual, $anterior) {
            if ($anterior == 0) return $actual > 0 ? 100.0 : null;
            return (($actual - $anterior) / abs($anterior)) * 100;
        };

        // Costos/margenes ocultos sin permiso explicito (confidencialidad)
        $puedeVerCostos = auth()->user()->havePermission('productos.ver_costos');

        $kpis = [
            'facturacion' => ['valor' => $fact,    'delta' => $delta($fact, $factAnt)],
            'ventas'      => ['valor' => $nVentas, 'delta' => $delta($nVentas, $nVentasAnt)],
            'ticket'      => ['valor' => $ticket,  'delta' => $delta($ticket, $ticketAnt)],
            'pedidosWeb'  => ['valor' => $pedidosWeb, 'delta' => $delta($pedidosWeb, $pedidosWebAnt), 'pendientes' => $pedidosWebPendientes],
            'margen'      => $puedeVerCostos ? ['valor' => $margen, 'pct' => $margenPct] : null,
            'compras'     => ['valor' => $compras],
        ];

        // ── Evolución dentro del período (por día si es corto, por mes si es largo) ──
        $porDia = $dias <= 62;
        $formatoSql  = $porDia ? '%Y-%m-%d' : '%Y-%m';

        $ventasSerie = $ventasValidas()
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw("DATE_FORMAT(fecha, '$formatoSql') as k, SUM(total_con_iva) as total")
            ->groupBy('k')->pluck('total', 'k');
        $webSerie = $pedidosWebQuery()
            ->whereBetween('order_date', [$desde, $hasta])
            ->selectRaw("DATE_FORMAT(order_date, '$formatoSql') as k, SUM(total_amount) as total")
            ->groupBy('k')->pluck('total', 'k');

        $ingresosSerieRaw = DB::table('movimientos')->where('tipo', 'ingreso')
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw("DATE_FORMAT(fecha, '$formatoSql') as k, SUM(total) as total")
            ->groupBy('k')->pluck('total', 'k');
        $egresosSerieRaw = DB::table('movimientos')->where('tipo', 'egreso')
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw("DATE_FORMAT(fecha, '$formatoSql') as k, SUM(total) as total")
            ->groupBy('k')->pluck('total', 'k');

        $labelsEvolucion = [];
        $serieVentas = [];
        $serieWeb = [];
        $serieIngresos = [];
        $serieEgresos = [];
        $cursor = $desde->copy();
        while ($cursor->lte($hasta)) {
            $k = $porDia ? $cursor->format('Y-m-d') : $cursor->format('Y-m');
            $labelsEvolucion[] = $porDia ? $cursor->format('d/m') : ucfirst($cursor->locale('es')->isoFormat('MMM YY'));
            $serieVentas[] = round((float) ($ventasSerie[$k] ?? 0), 2);
            $serieWeb[]    = round((float) ($webSerie[$k] ?? 0), 2);
            $serieIngresos[] = round((float) ($ingresosSerieRaw[$k] ?? 0), 2);
            $serieEgresos[]  = round((float) ($egresosSerieRaw[$k] ?? 0), 2);
            $porDia ? $cursor->addDay() : $cursor->addMonth()->startOfMonth();
        }

        // ── Canales (período) ────────────────────────────────────────
        $canalesNombres = [
            'tienda' => 'Tienda online', 'meli' => 'MercadoLibre', 'whatsapp' => 'WhatsApp',
            'instagram' => 'Instagram', 'facebook' => 'Facebook', 'local' => 'Local',
        ];
        $pedidosPorCanal = $pedidosWebQuery()
            ->whereBetween('order_date', [$desde, $hasta])
            ->groupBy('origen')
            ->selectRaw('origen, COUNT(*) as pedidos, COALESCE(SUM(total_amount),0) as facturado')
            ->orderByDesc('facturado')
            ->get()
            ->map(function ($row) use ($canalesNombres) {
                $row->nombre = $canalesNombres[$row->origen] ?? ucfirst($row->origen ?? 'tienda');
                return $row;
            });

        // ── Ventas y productos (período) ─────────────────────────────
        $detalleBase = fn () => DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.idventa', '=', 'dv.venta_id')
            ->join('productos as p', 'p.idarticulo', '=', 'dv.articulo_id')
            ->where('v.estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('v.fecha', [$desde, $hasta])
            ->when($sucursalesPermitidas, fn ($q, $s) => $q->whereIn('v.sucursal_id', $s));

        $topProductos = $detalleBase()
            ->groupBy('p.idarticulo', 'p.nombre')
            ->selectRaw('p.nombre, SUM(dv.cantidad) as unidades, SUM(dv.subtotal_con_iva) as facturado')
            ->orderByDesc('facturado')->limit(8)->get();

        $mixCategorias = $detalleBase()
            ->join('categorias as c', 'c.idcategoria', '=', 'p.categoria_id')
            ->groupBy('c.idcategoria', 'c.nombre')
            ->selectRaw('c.nombre, SUM(dv.subtotal_con_iva) as facturado')
            ->orderByDesc('facturado')->get();

        $mixPlazas = $detalleBase()
            ->whereNotNull('p.plazas')
            ->groupBy('p.plazas')
            ->selectRaw('p.plazas, SUM(dv.cantidad) as unidades')
            ->orderByDesc('unidades')->get()
            ->map(function ($row) {
                $row->plazas = \App\Models\Articulo::PLAZAS[$row->plazas] ?? $row->plazas;
                return $row;
            });

        // ── Clientes ─────────────────────────────────────────────────
        $clientesTotal     = (int) DB::table('clientes')->count();
        $clientesConCuenta = (int) DB::table('clientes')->whereNotNull('password')->count();

        $topClientes = DB::table('ventas as v')
            ->join('clientes as c', 'c.idcliente', '=', 'v.cliente_id')
            ->where('v.estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('v.fecha', [$desde, $hasta])
            ->groupBy('c.idcliente', 'c.nombre', 'c.paterno', 'c.materno')
            ->selectRaw("CONCAT(c.nombre, ' ', COALESCE(c.paterno,'')) as nombre, COUNT(*) as compras, SUM(v.total_con_iva) as facturado")
            ->orderByDesc('facturado')->limit(8)->get();

        // Cuenta corriente: deuda total y principales deudores (foto actual)
        $ccResumen = DB::table('cliente_cc_movimientos')
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo='cargo' THEN monto ELSE -monto END),0) as saldo")
            ->first();
        $deudaCC = max(0, (float) $ccResumen->saldo);

        $topDeudores = DB::table('cliente_cc_movimientos as m')
            ->join('clientes as c', 'c.idcliente', '=', 'm.cliente_id')
            ->groupBy('c.idcliente', 'c.nombre', 'c.paterno')
            ->selectRaw("c.idcliente, CONCAT(c.nombre,' ',COALESCE(c.paterno,'')) as nombre,
                COALESCE(SUM(CASE WHEN m.tipo='cargo' THEN m.monto ELSE -m.monto END),0) as saldo")
            ->havingRaw('saldo > 0')
            ->orderByDesc('saldo')->limit(6)->get();

        // ── Stock (foto actual) ──────────────────────────────────────
        $inv = DB::table('sucursal_articulo as sa')
            ->join('productos as p', 'p.idarticulo', '=', 'sa.articulo_id')
            ->where('sa.activo', 1)
            ->selectRaw('COALESCE(SUM(sa.stock),0) as unidades,
                         COALESCE(SUM(sa.stock * p.pcompra_con_iva),0) as valor_costo,
                         COALESCE(SUM(sa.stock * p.pventa_con_iva),0) as valor_venta')
            ->first();

        $stockCritico = DB::table('sucursal_articulo as sa')
            ->join('productos as p', 'p.idarticulo', '=', 'sa.articulo_id')
            ->where('sa.activo', 1)->where('p.estado', 'Activo')
            ->groupBy('p.idarticulo', 'p.nombre')
            ->havingRaw('SUM(sa.stock) <= 3')
            ->selectRaw('p.nombre, SUM(sa.stock) as stock')
            ->orderBy('stock')->limit(10)->get();

        $stockPorCategoria = DB::table('sucursal_articulo as sa')
            ->join('productos as p', 'p.idarticulo', '=', 'sa.articulo_id')
            ->join('categorias as c', 'c.idcategoria', '=', 'p.categoria_id')
            ->where('sa.activo', 1)
            ->groupBy('c.idcategoria', 'c.nombre')
            ->selectRaw('c.nombre, COALESCE(SUM(sa.stock),0) as unidades, COALESCE(SUM(sa.stock * p.pventa_con_iva),0) as valor')
            ->orderByDesc('valor')->get();

        // ── Finanzas: tesorería, gastos, devoluciones, deudas (período) ──
        $ingresosPeriodo = (float) DB::table('movimientos')->where('tipo', 'ingreso')
            ->whereBetween('fecha', [$desde, $hasta])->sum('total');
        $egresosPeriodo = (float) DB::table('movimientos')->where('tipo', 'egreso')
            ->whereBetween('fecha', [$desde, $hasta])->sum('total');
        $resultadoPeriodo = round($ingresosPeriodo - $egresosPeriodo, 2);

        $saldoTotalCuentas = (float) DB::table('movimientos')
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo='ingreso' THEN total ELSE -total END),0) as saldo")
            ->value('saldo');

        $gastosPeriodo = (float) DB::table('gastos')->where('estado', 'pagado')
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])->sum('monto');

        $gastosPorCategoriaPeriodo = DB::table('gastos as g')
            ->join('gasto_categorias as c', 'c.id', '=', 'g.gasto_categoria_id')
            ->where('g.estado', 'pagado')
            ->whereBetween('g.fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('c.id', 'c.nombre')
            ->selectRaw('c.nombre, SUM(g.monto) as total')
            ->orderByDesc('total')->get();

        $devolucionesPeriodo = DB::table('devoluciones')
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw("COUNT(*) as cantidad, COALESCE(SUM(monto),0) as monto")
            ->first();

        // Cuentas por pagar a proveedores: foto actual (no es del período, es deuda viva)
        $hoy = Carbon::today();
        $cxpVencidas = DB::table('proveedor_cc_movimientos as m')
            ->join('proveedores as p', 'p.idproveedor', '=', 'm.proveedor_id')
            ->where('m.tipo', 'debe')->where('m.estado', '!=', 'pagado')
            ->whereNotNull('m.fecha_vencimiento')->whereDate('m.fecha_vencimiento', '<', $hoy)
            ->groupBy('p.idproveedor', 'p.nombre')
            ->selectRaw('p.nombre, SUM(m.monto) as monto, MIN(m.fecha_vencimiento) as vencimiento')
            ->orderBy('vencimiento')->get();

        $cxpProximas = DB::table('proveedor_cc_movimientos as m')
            ->join('proveedores as p', 'p.idproveedor', '=', 'm.proveedor_id')
            ->where('m.tipo', 'debe')->where('m.estado', '!=', 'pagado')
            ->whereNotNull('m.fecha_vencimiento')
            ->whereBetween('m.fecha_vencimiento', [$hoy->toDateString(), $hoy->copy()->addDays(30)->toDateString()])
            ->groupBy('p.idproveedor', 'p.nombre')
            ->selectRaw('p.nombre, SUM(m.monto) as monto, MIN(m.fecha_vencimiento) as vencimiento')
            ->orderBy('vencimiento')->get();

        $cxpVencidoTotal  = (float) $cxpVencidas->sum('monto');
        $cxpProximasTotal = (float) $cxpProximas->sum('monto');

        // Comparación de precios de proveedores por categoría (últimos 12 meses,
        // no se limita al período del filtro para tener volumen suficiente de compras)
        $comparativaProveedores = DB::table('detalle_compras as dc')
            ->join('compras as co', 'co.idcompra', '=', 'dc.compra_id')
            ->join('productos as p', 'p.idarticulo', '=', 'dc.articulo_id')
            ->join('categorias as cat', 'cat.idcategoria', '=', 'p.categoria_id')
            ->join('proveedores as pr', 'pr.idproveedor', '=', 'co.proveedor_id')
            ->where('co.estado', 'NOT LIKE', 'Cancel%')
            ->where('co.fecha', '>=', now()->subMonths(12))
            ->groupBy('cat.idcategoria', 'cat.nombre', 'pr.idproveedor', 'pr.nombre')
            ->selectRaw('cat.nombre as categoria, pr.nombre as proveedor,
                AVG(dc.precio_unitario) as precio_promedio, SUM(dc.cantidad) as unidades')
            ->orderBy('cat.nombre')->orderBy('precio_promedio')
            ->get()
            ->groupBy('categoria')
            ->filter(fn ($grupo) => $grupo->pluck('proveedor')->unique()->count() > 1)
            ->values();

        return view('report.graph.index', compact(
            'desde', 'hasta', 'dias', 'kpis',
            'labelsEvolucion', 'serieVentas', 'serieWeb', 'serieIngresos', 'serieEgresos', 'pedidosPorCanal',
            'topProductos', 'mixCategorias', 'mixPlazas',
            'clientesTotal', 'clientesConCuenta', 'topClientes', 'deudaCC', 'topDeudores',
            'inv', 'stockCritico', 'stockPorCategoria',
            'ingresosPeriodo', 'egresosPeriodo', 'resultadoPeriodo', 'saldoTotalCuentas',
            'gastosPeriodo', 'gastosPorCategoriaPeriodo', 'devolucionesPeriodo',
            'cxpVencidas', 'cxpProximas', 'cxpVencidoTotal', 'cxpProximasTotal',
            'comparativaProveedores'
        ));
    }

    /**
     * Libro de movimientos: todos los ingresos, egresos, devoluciones y cobros
     * del período, en una sola lista. Descargable en CSV (?export=csv).
     */
    public function movimientos(Request $request)
    {
        Gate::authorize('haveaccess', 'reporte.index');

        $desde = $request->filled('desde') ? Carbon::parse($request->input('desde'))->startOfDay() : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->input('hasta'))->endOfDay()   : Carbon::now()->endOfDay();
        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        $movs = collect();

        // Ventas (ingresos)
        DB::table('ventas as v')
            ->leftJoin('clientes as c', 'c.idcliente', '=', 'v.cliente_id')
            ->where('v.estado', 'NOT LIKE', 'Cancel%')->where('v.estado', 'NOT LIKE', 'Anul%')
            ->whereBetween('v.fecha', [$desde, $hasta])
            ->selectRaw("v.fecha as fecha, v.num_folio, v.idventa, v.total_con_iva as monto, CONCAT(COALESCE(c.nombre,''),' ',COALESCE(c.paterno,'')) as detalle")
            ->get()
            ->each(function ($r) use ($movs) {
                $movs->push((object) [
                    'fecha' => $r->fecha, 'tipo' => 'Venta', 'signo' => 1,
                    'referencia' => $r->num_folio ?: ('Venta #' . $r->idventa),
                    'detalle' => trim($r->detalle) ?: 'Consumidor final',
                    'monto' => (float) $r->monto,
                ]);
            });

        // Compras (egresos)
        DB::table('compras as co')
            ->leftJoin('proveedores as pr', 'pr.idproveedor', '=', 'co.proveedor_id')
            ->where('co.estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('co.fecha', [$desde, $hasta])
            ->selectRaw('co.fecha as fecha, co.num_folio, co.idcompra, co.total_con_iva as monto, pr.nombre as detalle')
            ->get()
            ->each(function ($r) use ($movs) {
                $movs->push((object) [
                    'fecha' => $r->fecha, 'tipo' => 'Compra', 'signo' => -1,
                    'referencia' => $r->num_folio ?: ('Compra #' . $r->idcompra),
                    'detalle' => $r->detalle ?: 'Proveedor',
                    'monto' => (float) $r->monto,
                ]);
            });

        // Devoluciones (egresos)
        DB::table('devoluciones')
            ->whereBetween('fecha', [$desde, $hasta])
            ->get()
            ->each(function ($r) use ($movs) {
                $movs->push((object) [
                    'fecha' => $r->fecha, 'tipo' => 'Devolución', 'signo' => -1,
                    'referencia' => ucfirst($r->tipo ?? '') . ' #' . $r->referencia_id,
                    'detalle' => $r->motivo ?: '—',
                    'monto' => (float) $r->monto,
                ]);
            });

        // Cobros de cuenta corriente (ingresos) y cargos (informativos, no suman caja)
        DB::table('cliente_cc_movimientos as m')
            ->join('clientes as c', 'c.idcliente', '=', 'm.cliente_id')
            ->where('m.tipo', 'pago')
            ->whereBetween('m.created_at', [$desde, $hasta])
            ->selectRaw("m.created_at as fecha, m.concepto, m.monto, m.medio_pago, CONCAT(c.nombre,' ',COALESCE(c.paterno,'')) as detalle")
            ->get()
            ->each(function ($r) use ($movs) {
                $movs->push((object) [
                    'fecha' => $r->fecha, 'tipo' => 'Cobro CC', 'signo' => 1,
                    'referencia' => ucfirst($r->medio_pago ?: 'efectivo'),
                    'detalle' => trim($r->detalle) . ' — ' . $r->concepto,
                    'monto' => (float) $r->monto,
                ]);
            });

        // ── Movimientos de stock ─────────────────────────────────────
        // Salidas por venta
        DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.idventa', '=', 'dv.venta_id')
            ->join('productos as p', 'p.idarticulo', '=', 'dv.articulo_id')
            ->where('v.estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('v.fecha', [$desde, $hasta])
            ->selectRaw('v.fecha as fecha, v.num_folio, v.idventa, p.nombre as producto, dv.cantidad')
            ->get()
            ->each(function ($r) use ($movs) {
                $movs->push((object) [
                    'fecha' => $r->fecha, 'tipo' => 'Stock salida', 'signo' => 0,
                    'referencia' => $r->num_folio ?: ('Venta #' . $r->idventa),
                    'detalle' => $r->producto,
                    'monto' => null, 'cantidad' => -1 * (int) $r->cantidad,
                ]);
            });

        // Entradas por compra
        DB::table('detalle_compras as dc')
            ->join('compras as co', 'co.idcompra', '=', 'dc.compra_id')
            ->join('productos as p', 'p.idarticulo', '=', 'dc.articulo_id')
            ->where('co.estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('co.fecha', [$desde, $hasta])
            ->selectRaw('co.fecha as fecha, co.num_folio, co.idcompra, p.nombre as producto, dc.cantidad')
            ->get()
            ->each(function ($r) use ($movs) {
                $movs->push((object) [
                    'fecha' => $r->fecha, 'tipo' => 'Stock entrada', 'signo' => 0,
                    'referencia' => $r->num_folio ?: ('Compra #' . $r->idcompra),
                    'detalle' => $r->producto,
                    'monto' => null, 'cantidad' => (int) $r->cantidad,
                ]);
            });

        // Transferencias y ajustes manuales de stock
        DB::table('movimientos_stock as ms')
            ->join('productos as p', 'p.idarticulo', '=', 'ms.articulo_id')
            ->whereBetween('ms.created_at', [$desde, $hasta])
            ->selectRaw('ms.created_at as fecha, ms.tipo as t, ms.observacion, p.nombre as producto, ms.cantidad')
            ->get()
            ->each(function ($r) use ($movs) {
                $movs->push((object) [
                    'fecha' => $r->fecha, 'tipo' => 'Stock ajuste', 'signo' => 0,
                    'referencia' => ucfirst($r->t ?? 'movimiento'),
                    'detalle' => $r->producto . ($r->observacion ? ' — ' . $r->observacion : ''),
                    'monto' => null, 'cantidad' => (int) $r->cantidad,
                ]);
            });

        // Los movimientos de dinero no llevan cantidad
        $movs = $movs->map(function ($m) {
            if (!property_exists($m, 'cantidad')) $m->cantidad = null;
            return $m;
        })->sortByDesc('fecha')->values();

        $totIngresos = $movs->where('signo', 1)->sum('monto');
        $totEgresos  = $movs->where('signo', -1)->sum('monto');
        $neto        = $totIngresos - $totEgresos;
        $unidadesVendidas  = abs($movs->where('tipo', 'Stock salida')->sum('cantidad'));
        $unidadesCompradas = $movs->where('tipo', 'Stock entrada')->sum('cantidad');

        // ── Resumen mensual de todo lo que pasó ──────────────────────
        $resumenMensual = $movs->groupBy(fn ($m) => Carbon::parse($m->fecha)->format('Y-m'))
            ->map(function ($grupo, $ym) {
                return (object) [
                    'ym'        => $ym,
                    'mes'       => ucfirst(Carbon::parse($ym . '-01')->locale('es')->isoFormat('MMMM YYYY')),
                    'ventas'    => $grupo->where('tipo', 'Venta')->sum('monto'),
                    'cobros'    => $grupo->where('tipo', 'Cobro CC')->sum('monto'),
                    'compras'   => $grupo->where('tipo', 'Compra')->sum('monto'),
                    'devol'     => $grupo->where('tipo', 'Devolución')->sum('monto'),
                    'neto'      => $grupo->where('signo', 1)->sum('monto') - $grupo->where('signo', -1)->sum('monto'),
                    'uVendidas' => abs($grupo->where('tipo', 'Stock salida')->sum('cantidad')),
                    'uCompradas'=> $grupo->where('tipo', 'Stock entrada')->sum('cantidad'),
                ];
            })
            ->sortKeysDesc()
            ->values();

        // ── Descarga CSV (compatible con Excel) ─────────────────────
        if ($request->input('export') === 'csv') {
            $nombre = 'movimientos_' . $desde->format('Ymd') . '_' . $hasta->format('Ymd') . '.csv';
            $out = "\xEF\xBB\xBF"; // BOM UTF-8 para Excel
            $out .= "RESUMEN MENSUAL\r\n";
            $out .= "Mes;Ventas;Cobros CC;Compras;Devoluciones;Neto;Unid. vendidas;Unid. compradas\r\n";
            foreach ($resumenMensual as $rm) {
                $out .= implode(';', [
                    $rm->mes,
                    number_format($rm->ventas, 2, ',', ''),
                    number_format($rm->cobros, 2, ',', ''),
                    number_format($rm->compras, 2, ',', ''),
                    number_format($rm->devol, 2, ',', ''),
                    number_format($rm->neto, 2, ',', ''),
                    $rm->uVendidas,
                    $rm->uCompradas,
                ]) . "\r\n";
            }
            $out .= "\r\nMOVIMIENTOS\r\n";
            $out .= "Fecha;Tipo;Referencia;Detalle;Ingreso;Egreso;Cantidad\r\n";
            foreach ($movs as $m) {
                $out .= implode(';', [
                    Carbon::parse($m->fecha)->format('d/m/Y H:i'),
                    $m->tipo,
                    str_replace(';', ',', $m->referencia),
                    str_replace(';', ',', $m->detalle),
                    ($m->signo > 0 && $m->monto !== null) ? number_format($m->monto, 2, ',', '') : '',
                    ($m->signo < 0 && $m->monto !== null) ? number_format($m->monto, 2, ',', '') : '',
                    $m->cantidad !== null ? $m->cantidad : '',
                ]) . "\r\n";
            }
            $out .= ";;;;;;\r\n";
            $out .= "TOTALES;;;;" . number_format($totIngresos, 2, ',', '') . ";" . number_format($totEgresos, 2, ',', '') . ";\r\n";
            $out .= "NETO;;;;" . number_format($neto, 2, ',', '') . ";;\r\n";

            return response($out, 200, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
            ]);
        }

        return view('report.graph.movimientos', compact(
            'movs', 'desde', 'hasta', 'totIngresos', 'totEgresos', 'neto',
            'unidadesVendidas', 'unidadesCompradas', 'resumenMensual'
        ));
    }

    public function get_data(Request $request)
    {
        try {

            $rules = [
                'date_start' => 'required|date',
                'date_end' => 'required|date',
            ];
            $messages = [
                'date_start.required' => 'La fecha inicial es requerida',
                'date_end.required' => 'La fecha final es requerida',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {  
                return response()->json([
                    'estatus' => 'errorvalidacion',
                    'mensaje'=> $validator->errors()->all(),
                    'alert' => 'Debes de seleccionar fecha inicial y final'
                ]);
            }

            $total_general_ventas =  DB::table('corte_cajero_dia')
            ->whereBetween('fecha', [$request->date_start,$request->date_end])
            ->sum('total_acomulado');

            $get_data = DB::table('corte_cajero_dia')
            ->select('fecha',DB::raw('SUM(total_acomulado) as total_sales'))
            ->groupBy('fecha')
            ->whereBetween('fecha', [$request->date_start,$request->date_end])
            ->get();
            /***************************************** */
            $get_data_ecommerce = DB::table('aperturacajavirtual')
            ->select(DB::raw('DATE(start_date_time) as fecha'),DB::raw('SUM(total) as total_sales_ecommerce'))
            ->groupBy('start_date_time')
            ->whereBetween('start_date_time', [$request->date_start,$request->date_end])
            ->get();

            $total_general_ventas_ecommerce =  DB::table('aperturacajavirtual')
            ->whereBetween('start_date_time', [$request->date_start,$request->date_end])
            ->sum('total');

            $totalGlobal = $total_general_ventas + $total_general_ventas_ecommerce;

            return response()->json([
                "accion" => "get_for_day",
                "estatus" => 1,
                "dates"=>$get_data,
                "dates_ecommerce"=>$get_data_ecommerce,
                "total_general" =>$totalGlobal,
            ]);

        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                'estatus'=> 0,  
                'mensaje' => (array) $m,
            ]);
        }
    }

    public function get_data_mes(Request $request)
    {
        try {

            $rules = [
                'mes_start' => 'required|date',
                'mes_end' => 'required|date',
            ];
            $messages = [
                'mes_start.required' => 'El mes inicial es requerido',
                'mes_end.required' => 'El mes final es requerido',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {  
                return response()->json([
                    'estatus' => 'errorvalidacion',
                    'mensaje'=> $validator->errors()->all(),
                    'alert' => 'Debes de seleccionar un mes inicial y final'
                ]);
            }
            /*$date = Carbon::parse($request->mes_end);
            $month = $date->month;
            $year = $date->year;*/

            //->whereDate('fecha', '=', date('2021-08'))
            /*->whereYear('fecha', '=', date($year))
            ->whereMonth('fecha', '=', date($month))
            ->get();*/
            
            $startonlymes = Carbon::parse($request->mes_start)->startOfMonth()->toDateString();
            $endonlymes = Carbon::parse($request->mes_end)->endOfMonth()->toDateString();

            $get_data_for_mes = DB::table('corte_cajero_dia')
            ->select(
                DB::raw('SUM(total_acomulado) as total_sales'),
                DB::raw("DATE_FORMAT(fecha,'%M %Y') as months")
            )
            ->groupBy('months')
            ->whereBetween('fecha', [$startonlymes,$endonlymes])
            ->get();
            
            $total_general_monts =  DB::table('corte_cajero_dia')
            ->whereBetween('fecha', [$startonlymes,$endonlymes])
            ->sum('total_acomulado');

            /*$get_data_for_mes = DB::table('corte_cajero_dia')
            ->select('fecha',DB::raw('SUM(total_acomulado) as total_sales'))
            ->groupBy('fecha')
            ->whereBetween('fecha', [$startonlymes,$endonlymes])
            ->get();*/

            $get_data_for_mes_ecommerce = DB::table('aperturacajavirtual')
            ->select(
                DB::raw('SUM(total) as total_sales_ecommerce'),
                DB::raw("DATE_FORMAT(start_date_time,'%M %Y') as months")
            )
            ->groupBy('months')
            ->whereBetween('start_date_time', [$startonlymes,$endonlymes])
            ->get();

            $total_general_monts_ecommerce =  DB::table('aperturacajavirtual')
            ->whereBetween('start_date_time', [$startonlymes,$endonlymes])
            ->sum('total');

            $totalGlobal = $total_general_monts + $total_general_monts_ecommerce;

            return response()->json([
                "accion" => "get_for_month",
                "estatus" => 1,
                "endonlymes"=>$endonlymes,
                "startonlymes"=>$startonlymes,
                "dates_month"=>$get_data_for_mes,
                "total_general" =>$totalGlobal,
                "dates_month_ecommerce" => $get_data_for_mes_ecommerce
            ]);   

        } catch (\Throwable $th) {
            //throw $th;
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                'estatus'=> 0,  
                'mensaje' => (array) $m,
            ]);
        }
    }
}
