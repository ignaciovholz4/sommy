<?php

namespace App\Http\Controllers\Cuentas;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\CajaApertura;
use App\Models\Movimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CajaAperturaController extends Controller
{
    /**
     * Mostrar historial de aperturas/cierres de una cuenta tipo caja.
     */
    public function historial(Cuenta $cuenta)
    {
        $aperturas = CajaApertura::where('cuenta_id', $cuenta->id)
            ->orderByDesc('fecha_apertura')
            ->get();

        // Totales por sesión para render directo (sin DataTable)
        $resumenMovs = DB::table('movimientos')
            ->whereIn('caja_apertura_id', $aperturas->pluck('id'))
            ->selectRaw("caja_apertura_id,
                SUM(CASE WHEN tipo='ingreso' THEN total ELSE 0 END) as ingresos,
                SUM(CASE WHEN tipo='egreso' THEN total ELSE 0 END) as egresos,
                COUNT(*) as cant")
            ->groupBy('caja_apertura_id')
            ->get()->keyBy('caja_apertura_id');

        foreach ($aperturas as $ap) {
            $r = $resumenMovs->get($ap->id);
            $ap->mov_ingresos = round($r->ingresos ?? 0, 2);
            $ap->mov_egresos  = round($r->egresos ?? 0, 2);
            $ap->mov_cant     = $r->cant ?? 0;
            $ap->saldo_final  = round($ap->fondo_inicial + $ap->mov_ingresos - $ap->mov_egresos, 2);
        }

        $aperturaActiva = $aperturas->firstWhere('abierta', true);
        $kpiAbierta = !is_null($aperturaActiva);

        if ($aperturaActiva) {
            $movActivos  = Movimiento::where('caja_apertura_id', $aperturaActiva->id)->get();
            $kpiIngresos = round($movActivos->where('tipo', 'ingreso')->sum('total'), 2);
            $kpiEgresos  = round($movActivos->where('tipo', 'egreso')->sum('total'), 2);
            $kpiSaldo    = round($aperturaActiva->fondo_inicial + $kpiIngresos - $kpiEgresos, 2);
            $kpiMovCount = $movActivos->count();
            $kpiFondo    = $aperturaActiva->fondo_inicial;
        } else {
            $kpiIngresos = $kpiEgresos = $kpiSaldo = $kpiMovCount = $kpiFondo = 0;
        }

        $aperturaActivaId = $aperturaActiva->id ?? null;
        $kpiDesde = $aperturaActiva ? \Carbon\Carbon::parse($aperturaActiva->fecha_apertura) : null;

        return view('cuentas.historial', compact(
            'cuenta', 'aperturas', 'aperturaActivaId', 'kpiDesde',
            'kpiAbierta', 'kpiSaldo', 'kpiIngresos', 'kpiEgresos', 'kpiMovCount', 'kpiFondo'
        ));
    }

    /**
     * Abrir una nueva cuenta tipo caja.
     */
    public function abrir(Request $request, Cuenta $cuenta)
    {
        if (!$cuenta->esCaja()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'Solo las cuentas de tipo caja pueden abrirse.',
            ], 422);
        }

        // Validar que no haya una apertura activa
        $existeAbierta = CajaApertura::where('cuenta_id', $cuenta->id)
            ->where('abierta', true)
            ->whereNull('fecha_cierre')
            ->exists();

        if ($existeAbierta) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'La cuenta ya tiene una apertura activa.',
            ], 422);
        }

        $request->validate([
            'fondo_inicial' => 'required|numeric|min:0',
        ]);

        $apertura = CajaApertura::create([
            'cuenta_id'      => $cuenta->id,
            'fecha_apertura' => now(),
            'fondo_inicial'  => $request->fondo_inicial,
            'abierta'        => true,
        ]);

        return response()->json([
            'estado'   => 1,
            'mensaje'  => 'Cuenta abierta correctamente.',
            'apertura' => $apertura,
        ]);
    }

    /**
     * Cerrar la cuenta activa (solo si es caja).
     */
    public function cerrar(Cuenta $cuenta)
    {
        $apertura = CajaApertura::where('cuenta_id', $cuenta->id)
            ->where('abierta', true)
            ->whereNull('fecha_cierre')
            ->first();

        if (!$apertura) {
            return response()->json([
                'estado'  => 0,
                'mensaje' => 'No hay una apertura activa para esta cuenta.',
            ], 422);
        }

        $apertura->update([
            'fecha_cierre' => now(),
            'abierta'      => false,
        ]);

        return response()->json([
            'estado'   => 1,
            'mensaje'  => 'Cuenta cerrada correctamente.',
            'apertura' => $apertura,
        ]);
    }

    /**
     * Historial para datatables.
     */
    public function historialData(Cuenta $cuenta)
    {
        $aperturas = CajaApertura::where('cuenta_id', $cuenta->id);

        return datatables()->eloquent($aperturas)
            ->addColumn('ingresos', function($ap) {
                $v = DB::table('movimientos')->where('caja_apertura_id', $ap->id)->where('tipo', 'ingreso')->sum('total');
                return '<span class="text-success fw-bold">$'.number_format($v, 2).'</span>';
            })
            ->addColumn('egresos', function($ap) {
                $v = DB::table('movimientos')->where('caja_apertura_id', $ap->id)->where('tipo', 'egreso')->sum('total');
                return '<span class="text-danger fw-bold">$'.number_format($v, 2).'</span>';
            })
            ->addColumn('saldo_final', function($ap) {
                $neto = DB::table('movimientos')->where('caja_apertura_id', $ap->id)
                    ->selectRaw("SUM(CASE WHEN tipo='ingreso' THEN total ELSE -total END) as neto")->value('neto') ?? 0;
                $saldo = round($ap->fondo_inicial + $neto, 2);
                $cls   = $saldo >= 0 ? 'fw-bold' : 'fw-bold text-danger';
                return '<span class="'.$cls.'">$'.number_format($saldo, 2).'</span>';
            })
            ->addColumn('cant_movimientos', function($ap) {
                return DB::table('movimientos')->where('caja_apertura_id', $ap->id)->count();
            })
            ->addColumn('estado', function($ap){
                return $ap->estaAbierta()
                    ? '<span class="badge bg-success">Abierta</span>'
                    : '<span class="badge bg-secondary">Cerrada</span>';
            })
            ->addColumn('acciones', function($ap){
                $acciones = '';
                if ($ap->estaAbierta()) {
                    $acciones .= '<button class="btn btn-sm btn-warning btn-cerrar" data-id="'.$ap->cuenta_id.'">
                                    <i class="fas fa-door-closed"></i> Cerrar
                                  </button>';
                } else {
                    $acciones .= '<button class="btn btn-sm btn-outline-info btn-resumen" data-apertura-id="'.$ap->id.'">
                                    <i class="fas fa-file-alt"></i> Resumen
                                  </button>';
                }
                $acciones .= ' <a href="'.route('cuentas.movimientos.index', ['cuenta' => $ap->cuenta_id, 'apertura' => $ap->id]).'"
                                class="btn btn-sm btn-outline-primary ms-1">
                                <i class="fas fa-list"></i> Movimientos
                              </a>';

                return $acciones;
            })
            ->rawColumns(['ingresos', 'egresos', 'saldo_final', 'estado', 'acciones'])
            ->toJson();
    }

    /**
     * Listar todas las cuentas abiertas actualmente (solo cajas).
     */
    public function abiertas()
    {
        $aperturas = CajaApertura::with('cuenta.moneda')
            ->where('abierta', true)
            ->whereNull('fecha_cierre')
            ->get();

        $data = $aperturas->map(function ($ap) {
            return [
                'id'             => $ap->id,
                'nombre'         => $ap->cuenta->nombre,
                'fecha_apertura' => $ap->fecha_apertura->format('d/m/Y H:i'),
                'moneda'         => $ap->cuenta->moneda->codigo,
            ];
        });

        return response()->json($data);
    }

    public function resumen(CajaApertura $apertura)
    {
        try {
            $inicio = (float) $apertura->fondo_inicial;

            // Movimientos vinculados a esta apertura
            $movimientos = Movimiento::where('caja_apertura_id', $apertura->id)->get();

            $ingresos = $movimientos->where('tipo', 'ingreso')->sum('total');
            $egresos  = $movimientos->where('tipo', 'egreso')->sum('total');

            $total = $ingresos + $egresos;
            $final = $inicio + $ingresos - $egresos;

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