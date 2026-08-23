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

        $movimientos = Movimiento::with('cuenta')
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderByDesc('fecha')
            ->get();

        $totales = [
            'ingresos'  => (float) $movimientos->where('tipo', 'ingreso')->sum('total'),
            'egresos'   => (float) $movimientos->where('tipo', 'egreso')->sum('total'),
            'efectivo'  => (float) $movimientos->sum('efectivo'),
            'bancos'    => (float) $movimientos->sum('bancos'),
            'tarjetas'  => (float) $movimientos->sum('tarjetas'),
            'cheques'   => (float) $movimientos->sum('cheques'),
        ];
        $totales['neto'] = $totales['ingresos'] - $totales['egresos'];

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
            'fleterosEfectivo' => $fleterosEfectivo,
        ]);
    }
}
