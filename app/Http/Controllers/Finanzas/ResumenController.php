<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Movimiento;
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

        $fleterosEfectivo = DB::table('entregas_fletero as ef')
            ->join('transportistas as t', 't.id', '=', 'ef.transportista_id')
            ->where('ef.rendido', false)
            ->groupBy('t.id', 't.nombre')
            ->selectRaw('t.id, t.nombre, SUM(ef.monto_cobrado) as pendiente')
            ->havingRaw('SUM(ef.monto_cobrado) > 0')
            ->get();

        return view('finanzas.resumen.index', [
            'periodo'          => $periodo,
            'desde'            => $desde,
            'hasta'            => $hasta,
            'movimientos'      => $movimientos,
            'totales'          => $totales,
            'totalesPorMoneda' => $totalesPorMoneda,
            'fleterosEfectivo' => $fleterosEfectivo,
        ]);
    }
}
