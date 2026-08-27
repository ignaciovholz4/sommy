<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\ProveedorCcMovimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Tablero financiero de solo lectura: ingresos/egresos, gastos por categoría,
 * saldos de tesorería, cuentas por pagar y por cobrar.
 */
class FinanzasDashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'finanzas.dashboard');

        $hoy         = Carbon::today();
        $inicioMes   = $hoy->copy()->startOfMonth();
        $hace12Meses = $hoy->copy()->startOfMonth()->subMonths(11);

        // (a) Ingresos vs egresos por mes, últimos 12 meses.
        // Solo cuentas en ARS: mezclar pesos y moneda extranjera sin convertir
        // daría un número sin sentido. Las tenencias en otra moneda se muestran
        // aparte, más abajo (saldosMonedaExtranjera).
        $monedaArsId = DB::table('monedas')->where('codigo', 'ARS')->value('id');

        // LEFT JOIN + moneda_id IS NULL: un movimiento sin cuenta (ej. pago con
        // un cheque de cartera endosado) se cuenta como ARS por defecto, igual
        // que antes de este fix — solo se excluyen las cuentas en otra moneda.
        $porMes = DB::table('movimientos as m')
            ->leftJoin('cuentas as c', 'c.id', '=', 'm.cuenta_id')
            ->selectRaw("DATE_FORMAT(m.fecha, '%Y-%m') as mes, m.tipo, SUM(m.total) as total")
            ->where('m.fecha', '>=', $hace12Meses)
            ->where(function ($q) use ($monedaArsId) {
                $q->whereNull('c.moneda_id')->orWhere('c.moneda_id', $monedaArsId);
            })
            ->groupBy('mes', 'm.tipo')
            ->get();

        $mesesLabels   = [];
        $serieIngresos = [];
        $serieEgresos  = [];
        $nombresMeses  = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        for ($i = 0; $i < 12; $i++) {
            $mes   = $hace12Meses->copy()->addMonths($i);
            $clave = $mes->format('Y-m');

            $mesesLabels[]   = $nombresMeses[$mes->month - 1] . ' ' . $mes->format('y');
            $serieIngresos[] = (float) $porMes->where('mes', $clave)->where('tipo', 'ingreso')->sum('total');
            $serieEgresos[]  = (float) $porMes->where('mes', $clave)->where('tipo', 'egreso')->sum('total');
        }

        // (b) Gastos pagados por categoría en el mes actual
        $gastosPorCategoria = DB::table('gastos as g')
            ->join('gasto_categorias as c', 'c.id', '=', 'g.gasto_categoria_id')
            ->where('g.estado', 'pagado')
            ->whereBetween('g.fecha', [$inicioMes->toDateString(), $hoy->toDateString()])
            ->groupBy('c.id', 'c.nombre')
            ->selectRaw('c.nombre, SUM(g.monto) as total')
            ->orderByDesc('total')
            ->get();

        // (c) Saldos por cuenta (ingresos - egresos de cada cuenta activa), con su moneda
        $cuentasActivas = Cuenta::with(['sucursal', 'moneda'])
            ->where('activa', true)
            ->get()
            ->map(function ($cuenta) {
                $ingresos = (float) $cuenta->movimientos()->where('tipo', 'ingreso')->sum('total');
                $egresos  = (float) $cuenta->movimientos()->where('tipo', 'egreso')->sum('total');

                return [
                    'nombre' => $cuenta->nombre . ' (' . ($cuenta->tipo === 'caja' ? 'Caja' : 'Banco') . ($cuenta->sucursal ? ' · ' . $cuenta->sucursal->nombre : '') . ')',
                    'saldo'  => round($ingresos - $egresos, 2),
                    'moneda_codigo'  => optional($cuenta->moneda)->codigo ?? 'ARS',
                    'moneda_simbolo' => optional($cuenta->moneda)->simbolo ?? '$',
                ];
            });

        $saldosCuentas = $cuentasActivas
            ->where('moneda_codigo', 'ARS')
            ->sortByDesc('saldo')
            ->values();

        // Tenencias en moneda extranjera: no se pueden sumar entre sí (USD != USDT),
        // se agrupan por moneda y se listan aparte del flujo de caja en pesos.
        $saldosMonedaExtranjera = $cuentasActivas
            ->where('moneda_codigo', '!=', 'ARS')
            ->groupBy('moneda_codigo')
            ->map(function ($cuentas, $codigo) {
                return [
                    'codigo'  => $codigo,
                    'simbolo' => $cuentas->first()['moneda_simbolo'],
                    'total'   => round($cuentas->sum('saldo'), 2),
                    'cuentas' => $cuentas->values(),
                ];
            })
            ->values();

        // (d) Cuentas por pagar
        $cxpDebe  = (float) ProveedorCcMovimiento::where('tipo', 'debe')->sum('monto');
        $cxpHaber = (float) ProveedorCcMovimiento::where('tipo', 'haber')->sum('monto');
        $cxpTotal = round($cxpDebe - $cxpHaber, 2);

        $cxpVencidas = ProveedorCcMovimiento::with('proveedor')
            ->where('tipo', 'debe')
            ->where('estado', '!=', 'pagado')
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->orderBy('fecha_vencimiento')
            ->get();

        $cxpProximas = ProveedorCcMovimiento::with('proveedor')
            ->where('tipo', 'debe')
            ->where('estado', '!=', 'pagado')
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$hoy->toDateString(), $hoy->copy()->addDays(30)->toDateString()])
            ->orderBy('fecha_vencimiento')
            ->get();

        $cxpVencidoTotal  = (float) $cxpVencidas->sum('monto');
        $cxpProximasTotal = (float) $cxpProximas->sum('monto');

        // (e) Cuentas por cobrar de clientes (cargos - pagos)
        $cxcTotales = DB::table('cliente_cc_movimientos')
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'cargo' THEN monto ELSE 0 END), 0) as cargos,
                COALESCE(SUM(CASE WHEN tipo = 'pago' THEN monto ELSE 0 END), 0) as pagos")
            ->first();
        $cxcSaldo = round((float) $cxcTotales->cargos - (float) $cxcTotales->pagos, 2);

        // (f) Resultado del mes actual (ingresos - egresos de tesorería en ARS)
        $delMes = DB::table('movimientos as m')
            ->leftJoin('cuentas as c', 'c.id', '=', 'm.cuenta_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN m.tipo = 'ingreso' THEN m.total ELSE 0 END), 0) as ingresos,
                COALESCE(SUM(CASE WHEN m.tipo = 'egreso' THEN m.total ELSE 0 END), 0) as egresos")
            ->where('m.fecha', '>=', $inicioMes)
            ->where(function ($q) use ($monedaArsId) {
                $q->whereNull('c.moneda_id')->orWhere('c.moneda_id', $monedaArsId);
            })
            ->first();

        $ingresosMes  = (float) $delMes->ingresos;
        $egresosMes   = (float) $delMes->egresos;
        $resultadoMes = round($ingresosMes - $egresosMes, 2);

        // (g) Resultado en moneda extranjera: mismo criterio (ingresos - egresos de
        // tesorería) que el de arriba, pero sin convertir, ya que estos montos ya
        // están en su moneda nativa. "Acumulado" es el histórico completo (no solo
        // el mes) porque lo que se busca es cuánto se viene ganando/gastando en esa
        // moneda desde que se empezó a operar con ella, no solo este mes.
        $resultadoExtranjero = function ($desde = null) {
            $q = DB::table('movimientos as m')
                ->join('cuentas as c', 'c.id', '=', 'm.cuenta_id')
                ->join('monedas as mo', 'mo.id', '=', 'c.moneda_id')
                ->where('mo.codigo', '!=', 'ARS');
            if ($desde) {
                $q->where('m.fecha', '>=', $desde);
            }
            return $q->groupBy('mo.codigo', 'mo.simbolo')
                ->selectRaw("mo.codigo, mo.simbolo,
                    COALESCE(SUM(CASE WHEN m.tipo = 'ingreso' THEN m.total ELSE 0 END), 0) as ingresos,
                    COALESCE(SUM(CASE WHEN m.tipo = 'egreso' THEN m.total ELSE 0 END), 0) as egresos")
                ->get()
                ->keyBy('codigo');
        };

        $extranjeroMes  = $resultadoExtranjero($inicioMes);
        $extranjeroTodo = $resultadoExtranjero();

        $resultadoExtranjeroMonedas = $extranjeroTodo->map(function ($r, $codigo) use ($extranjeroMes) {
            $mes = $extranjeroMes->get($codigo);
            return [
                'codigo'            => $r->codigo,
                'simbolo'           => $r->simbolo,
                'ingresos_mes'      => round((float) ($mes->ingresos ?? 0), 2),
                'egresos_mes'       => round((float) ($mes->egresos ?? 0), 2),
                'resultado_mes'     => round((float) ($mes->ingresos ?? 0) - (float) ($mes->egresos ?? 0), 2),
                'resultado_total'   => round((float) $r->ingresos - (float) $r->egresos, 2),
            ];
        })->values();

        return view('finanzas.dashboard', [
            'mesesLabels'        => $mesesLabels,
            'serieIngresos'      => $serieIngresos,
            'serieEgresos'       => $serieEgresos,
            'gastosPorCategoria' => $gastosPorCategoria,
            'saldosCuentas'      => $saldosCuentas,
            'saldosMonedaExtranjera' => $saldosMonedaExtranjera,
            'cxpTotal'           => $cxpTotal,
            'cxpVencidas'        => $cxpVencidas,
            'cxpProximas'        => $cxpProximas,
            'cxpVencidoTotal'    => $cxpVencidoTotal,
            'cxpProximasTotal'   => $cxpProximasTotal,
            'cxcSaldo'           => $cxcSaldo,
            'ingresosMes'        => $ingresosMes,
            'egresosMes'         => $egresosMes,
            'resultadoMes'       => $resultadoMes,
            'resultadoExtranjeroMonedas' => $resultadoExtranjeroMonedas,
        ]);
    }
}
