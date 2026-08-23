<?php

namespace App\Http\Controllers\Cuentas;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Moneda;
use App\Models\Sucursal;
use App\Models\CajaApertura;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CuentasController extends Controller
{
    /**
     * Listado de cuentas (pueden ser cajas o bancos)
     */
    public function index()
    {
        $cuentas = Cuenta::with('moneda','sucursal')
            ->orderByDesc('activa')->orderBy('nombre')
            ->get();
        $monedas = Moneda::all();
        $sucursales = Sucursal::all();

        // Saldo, apertura y último movimiento por cuenta (para las tarjetas)
        $aperturasAbiertas = CajaApertura::where('abierta', true)
            ->whereNull('fecha_cierre')->get()->keyBy('cuenta_id');

        foreach ($cuentas as $cuenta) {
            $apertura = $aperturasAbiertas->get($cuenta->id);
            $cuenta->apertura_abierta = $cuenta->tipo === 'caja' && $apertura !== null;

            if ($cuenta->tipo === 'caja') {
                if ($apertura) {
                    $neto = DB::table('movimientos')
                        ->where('caja_apertura_id', $apertura->id)
                        ->selectRaw("SUM(CASE WHEN tipo='ingreso' THEN total ELSE -total END) as neto")
                        ->value('neto') ?? 0;
                    $cuenta->saldo_actual = round($apertura->fondo_inicial + $neto, 2);
                } else {
                    $cuenta->saldo_actual = null; // caja sin apertura activa
                }
            } else {
                $neto = DB::table('movimientos')
                    ->where('cuenta_id', $cuenta->id)
                    ->selectRaw("SUM(CASE WHEN tipo='ingreso' THEN total ELSE -total END) as neto")
                    ->value('neto') ?? 0;
                $cuenta->saldo_actual = round($neto, 2);
            }

            $cuenta->ultimo_mov = DB::table('movimientos')
                ->where('cuenta_id', $cuenta->id)
                ->orderByDesc('fecha')
                ->first(['fecha', 'tipo', 'total']);
        }

        return view('cuentas.index', compact('cuentas', 'monedas', 'sucursales'));
    }

    /**
     * Crear nueva cuenta
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'sucursal_id'  => 'required|exists:sucursales,id',
            'moneda_id'    => 'required|exists:monedas,id',
            'tipo'         => 'required|in:caja,banco,tercero',
            'activa'       => 'boolean',
        ]);

        Cuenta::create([
            'nombre'      => $request->nombre,
            'sucursal_id' => $request->sucursal_id,
            'moneda_id'   => $request->moneda_id,
            'tipo'        => $request->tipo,
            'activa'      => $request->has('activa'),
        ]);

        return redirect()->route('cuentas.index')->with('success','Cuenta creada correctamente');
    }

    /**
     * Actualizar atributos de una cuenta
     */
    public function update(Request $request, Cuenta $cuenta)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $cuenta->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('cuentas.index')
                        ->with('success','Cuenta actualizada correctamente');
    }

    /**
     * Listado para datatables
     */
    public function list(Request $request)
    {
        $cuentas = Cuenta::with(['sucursal','moneda']);

        return datatables()->eloquent($cuentas)
            ->addColumn('apertura_estado', function($cuenta) {
                if ($cuenta->tipo !== 'caja') return '—';
                $abierta = CajaApertura::where('cuenta_id', $cuenta->id)
                    ->where('abierta', true)->whereNull('fecha_cierre')->exists();
                return $abierta
                    ? '<span class="badge bg-success">Abierta</span>'
                    : '<span class="badge bg-secondary">Cerrada</span>';
            })
            ->addColumn('saldo_actual', function($cuenta) {
                if ($cuenta->tipo === 'caja') {
                    $apertura = CajaApertura::where('cuenta_id', $cuenta->id)
                        ->where('abierta', true)->whereNull('fecha_cierre')->first();
                    if (!$apertura) return '<span class="text-muted">—</span>';
                    $neto = DB::table('movimientos')
                        ->where('caja_apertura_id', $apertura->id)
                        ->selectRaw("SUM(CASE WHEN tipo='ingreso' THEN total ELSE -total END) as neto")
                        ->value('neto') ?? 0;
                    $saldo = round($apertura->fondo_inicial + $neto, 2);
                } else {
                    $neto = DB::table('movimientos')
                        ->where('cuenta_id', $cuenta->id)
                        ->selectRaw("SUM(CASE WHEN tipo='ingreso' THEN total ELSE -total END) as neto")
                        ->value('neto') ?? 0;
                    $saldo = round($neto, 2);
                }
                $cls = $saldo >= 0 ? 'fw-bold' : 'fw-bold text-danger';
                return '<span class="'.$cls.'">$'.number_format($saldo, 2).'</span>';
            })
            ->addColumn('ultimo_mov', function($cuenta) {
                $mov = DB::table('movimientos')
                    ->where('cuenta_id', $cuenta->id)
                    ->orderByDesc('fecha')
                    ->first(['fecha', 'tipo', 'total']);
                if (!$mov) return '<span class="text-muted small">Sin movimientos</span>';
                $cls   = $mov->tipo === 'ingreso' ? 'text-success' : 'text-danger';
                $fecha = \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y');
                return '<span class="'.$cls.' small fw-bold">$'.number_format($mov->total, 2).'</span><br>'
                      .'<small class="text-muted">'.$fecha.'</small>';
            })
            ->addColumn('action', function($cuenta){
                $btnEdit = '<button class="btn btn-sm btn-primary btn-edit"
                                data-id="'.$cuenta->id.'"
                                data-nombre="'.$cuenta->nombre.'"
                                data-activa="'.$cuenta->activa.'">
                                <i class="fas fa-edit"></i>
                            </button>';

                if ($cuenta->activa) {
                    $btnToggle = '<button class="btn btn-sm btn-danger btn-deactivate"
                                    data-id="'.$cuenta->id.'">
                                    <i class="fas fa-trash"></i>
                                </button>';
                } else {
                    $btnToggle = '<button class="btn btn-sm btn-success btn-activate"
                                    data-id="'.$cuenta->id.'">
                                    <i class="fas fa-check"></i>
                                </button>';
                }

                if ($cuenta->tipo === 'caja') {
                    $btnHistorial = '<a href="'.route('cuentas.historial', $cuenta->id).'"
                                        class="btn btn-sm btn-info" title="Historial de Aperturas/Cierres">
                                        <i class="fas fa-door-open"></i>
                                    </a>';
                } else {
                    $btnHistorial = '<a href="'.route('cuentas.movimientos.index', $cuenta->id).'"
                                        class="btn btn-sm btn-info" title="Movimientos">
                                        <i class="fas fa-list"></i>
                                    </a>';
                }

                return $btnEdit . ' ' . $btnToggle . ' ' . $btnHistorial;
            })
            ->rawColumns(['apertura_estado', 'saldo_actual', 'ultimo_mov', 'action'])
            ->toJson();
    }



    public function desactivar(Cuenta $cuenta)
    {
        $cuenta->activa = 0;
        $cuenta->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Cuenta desactivada correctamente'
        ]);
    }

    public function activar(Cuenta $cuenta)
    {
        $cuenta->activa = 1;
        $cuenta->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Cuenta activada correctamente'
        ]);
    }

    /**
     * Panel de control de plata en cuentas de terceros: cada movimiento
     * registra el alias/CUIT al que fue el dinero, acá se agrupa por
     * alias+CUIT con totales y se puede filtrar.
     */
    public function terceros()
    {
        return view('cuentas.terceros');
    }

    public function tercerosData(Request $request)
    {
        $query = DB::table('movimientos')
            ->whereNotNull('alias_tercero')
            ->where('alias_tercero', '!=', '');

        if ($request->filled('alias')) {
            $query->where('alias_tercero', 'like', '%' . $request->alias . '%');
        }
        if ($request->filled('cuit')) {
            // Se compara sin guiones para que "20-12345678-9" matchee "20123456789"
            $cuitLimpio = preg_replace('/[^0-9]/', '', $request->cuit);
            $query->whereRaw("REPLACE(REPLACE(cuit_tercero, '-', ''), ' ', '') LIKE ?", ['%' . $cuitLimpio . '%']);
        }
        if ($request->filled('desde')) {
            $query->whereDate('fecha', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha', '<=', $request->hasta);
        }

        $resumen = (clone $query)
            ->selectRaw("
                LOWER(TRIM(alias_tercero)) as alias,
                MAX(cuit_tercero) as cuit,
                SUM(CASE WHEN tipo = 'ingreso' THEN total ELSE 0 END) as ingresos,
                SUM(CASE WHEN tipo = 'egreso' THEN total ELSE 0 END) as egresos,
                SUM(CASE WHEN tipo = 'ingreso' THEN total ELSE -total END) as neto,
                COUNT(*) as cantidad,
                MAX(fecha) as ultimo
            ")
            ->groupBy(DB::raw('LOWER(TRIM(alias_tercero))'))
            ->orderByDesc('neto')
            ->get();

        $totales = [
            'ingresos' => (float) $resumen->sum('ingresos'),
            'egresos'  => (float) $resumen->sum('egresos'),
            'neto'     => (float) $resumen->sum('neto'),
            'aliases'  => $resumen->count(),
        ];

        return response()->json(['estado' => 1, 'resumen' => $resumen, 'totales' => $totales]);
    }

    /** Movimientos individuales de un alias (detalle al hacer click) */
    public function tercerosMovimientos(Request $request)
    {
        $movs = DB::table('movimientos')
            ->leftJoin('cuentas', 'cuentas.id', '=', 'movimientos.cuenta_id')
            ->whereRaw('LOWER(TRIM(movimientos.alias_tercero)) = ?', [mb_strtolower(trim($request->alias ?? ''))])
            ->orderByDesc('movimientos.fecha')
            ->limit(300)
            ->get([
                'movimientos.fecha', 'movimientos.tipo', 'movimientos.medio',
                'movimientos.cliente_proveedor', 'movimientos.comprobante',
                'movimientos.observaciones', 'movimientos.total',
                'movimientos.cuit_tercero', 'cuentas.nombre as cuenta',
            ]);

        return response()->json(['estado' => 1, 'movimientos' => $movs]);
    }

    /** Alias/CUIT ya usados, para autocompletar en los formularios de cobro */
    public function tercerosAlias()
    {
        $alias = DB::table('movimientos')
            ->whereNotNull('alias_tercero')
            ->where('alias_tercero', '!=', '')
            ->selectRaw('LOWER(TRIM(alias_tercero)) as alias, MAX(cuit_tercero) as cuit, MAX(fecha) as ultimo')
            ->groupBy(DB::raw('LOWER(TRIM(alias_tercero))'))
            ->orderByDesc('ultimo')
            ->limit(100)
            ->get(['alias', 'cuit']);

        return response()->json(['estado' => 1, 'alias' => $alias]);
    }
}