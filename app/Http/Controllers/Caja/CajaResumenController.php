<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Models\CajaApertura;
use App\Models\Movimiento;
use Illuminate\Support\Facades\DB;

class CajaResumenController extends Controller
{
    public function show(CajaApertura $apertura)
    {
        try {
            $inicio = (float) $apertura->fondo_inicial;

            // Movimientos de la apertura
            $movimientos = Movimiento::where('caja_apertura_id', $apertura->id)->get();

            // Sumas
            $ingresos = $movimientos->where('tipo', 'ingreso')->sum('total');
            $egresos  = $movimientos->where('tipo', 'egreso')->sum('total');

            // Total de operaciones
            $total = $ingresos + $egresos;

            // Final de caja
            $final = $inicio + $ingresos - $egresos;

            // Faltante / sobrante
            $sobrante = $final > $total ? round($final - $total, 2) : 0.00;
            $faltante = $final < $total ? round($total - $final, 2) : 0.00;

            $settings = DB::table('configuracion')->where('id', 1)->first();

            return response()->json([
                'status'       => 1,
                'mensaje'      => 'ok',
                'inicio'       => number_format($inicio, 2, '.', ''),
                'ingresos'     => number_format($ingresos, 2, '.', ''),
                'egresos'      => number_format($egresos, 2, '.', ''),
                'total'        => number_format($total, 2, '.', ''),
                'final'        => number_format($final, 2, '.', ''),
                'sobrante'     => number_format($sobrante, 2, '.', ''),
                'faltante'     => number_format($faltante, 2, '.', ''),
                'name_cajero'  => auth()->user()->name ?? '—',
                'cierre'       => optional($apertura->fecha_cierre)->toDateTimeString(),
                'apert'        => $apertura->id,
                'settings'     => $settings,
                'class'        => 'success',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 0,
                'message' => 'Ocurrió un error',
            ], 500);
        }
    }
}