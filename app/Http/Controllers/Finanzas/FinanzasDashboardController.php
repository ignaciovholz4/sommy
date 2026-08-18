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

        // (a) Ingresos vs egresos por mes, últimos 12 meses
        $porMes = DB::table('movimientos')
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes, tipo, SUM(total) as total")
            ->where('fecha', '>=', $hace12Meses)
            ->groupBy('mes', 'tipo')
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

        // (c) Saldos por cuenta (ingresos - egresos de cada cuenta activa)
        $saldosCuentas = Cuenta::with('sucursal')
            ->where('activa', true)
            ->get()
            ->map(function ($cuenta) {
                $ingresos = (float) $cuenta->movimientos()->where('tipo', 'ingreso')->sum('total');
                $egresos  = (float) $cuenta->movimientos()->where('tipo', 'egreso')->sum('total');

                return [
                    'nombre' => $cuenta->nombre . ' (' . ($cuenta->tipo === 'caja' ? 'Caja' : 'Banco') . ($cuenta->sucursal ? ' · ' . $cuenta->sucursal->nombre : '') . ')',
                    'saldo'  => round($ingresos - $egresos, 2),
                ];
            })
            ->sortByDesc('saldo')
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

        // (f) Resultado del mes actual (ingresos - egresos de tesorería)
        $delMes = DB::table('movimientos')
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN total ELSE 0 END), 0) as ingresos,
                COALESCE(SUM(CASE WHEN tipo = 'egreso' THEN total ELSE 0 END), 0) as egresos")
            ->where('fecha', '>=', $inicioMes)
            ->first();

        $ingresosMes  = (float) $delMes->ingresos;
        $egresosMes   = (float) $delMes->egresos;
        $resultadoMes = round($ingresosMes - $egresosMes, 2);

        return view('finanzas.dashboard', [
            'mesesLabels'        => $mesesLabels,
            'serieIngresos'      => $serieIngresos,
            'serieEgresos'       => $serieEgresos,
            'gastosPorCategoria' => $gastosPorCategoria,
            'saldosCuentas'      => $saldosCuentas,
            'cxpTotal'           => $cxpTotal,
            'cxpVencidas'        => $cxpVencidas,
            'cxpProximas'        => $cxpProximas,
            'cxpVencidoTotal'    => $cxpVencidoTotal,
            'cxpProximasTotal'   => $cxpProximasTotal,
            'cxcSaldo'           => $cxcSaldo,
            'ingresosMes'        => $ingresosMes,
            'egresosMes'         => $egresosMes,
            'resultadoMes'       => $resultadoMes,
        ]);
    }
}
