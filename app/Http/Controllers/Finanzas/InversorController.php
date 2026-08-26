<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\CajaApertura;
use App\Models\Inversor;
use App\Models\InversorMovimiento;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Inversores: aportes, retiros y reparto de ganancias por % de participación.
 * Cuando el movimiento sale/entra de una caja o banco real (cuenta_ref
 * indicado), se crea también un Movimiento de tesorería — mismo patrón que
 * gastos/cheques/CxP — para que el saldo de esa cuenta lo refleje.
 */
class InversorController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'inversores.index');

        $inversores = Inversor::orderByDesc('activo')->orderBy('nombre')->get()->map(function (Inversor $inv) {
            $aportes = (float) $inv->movimientos()->where('tipo', 'aporte')->sum('monto');
            $retiros = (float) $inv->movimientos()->whereIn('tipo', ['retiro', 'distribucion'])->sum('monto');
            $inv->saldo = round($aportes - $retiros, 2);
            return $inv;
        });

        $totalAportado = $inversores->sum(fn ($i) => (float) $i->movimientos()->where('tipo', 'aporte')->sum('monto'));

        return view('finanzas.inversores.index', compact('inversores', 'totalAportado'));
    }

    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'inversores.crud');

        $request->validate([
            'nombre' => 'required|string|max:200',
            'porcentaje_participacion' => 'nullable|numeric|min:0|max:100',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
        ]);

        Inversor::create($request->only('nombre', 'porcentaje_participacion', 'telefono', 'email'));

        return back()->with('inversor_ok', 'Inversor agregado correctamente.');
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'inversores.crud');

        $request->validate([
            'nombre' => 'required|string|max:200',
            'porcentaje_participacion' => 'nullable|numeric|min:0|max:100',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'activo' => 'nullable|boolean',
        ]);

        $inversor = Inversor::findOrFail($id);
        $inversor->update([
            'nombre' => $request->nombre,
            'porcentaje_participacion' => $request->porcentaje_participacion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'activo' => $request->boolean('activo', true),
        ]);

        return back()->with('inversor_ok', 'Inversor actualizado.');
    }

    public function destroy($id)
    {
        Gate::authorize('haveaccess', 'inversores.crud');

        Inversor::findOrFail($id)->delete();

        return back()->with('inversor_ok', 'Inversor eliminado.');
    }

    public function ficha($id)
    {
        Gate::authorize('haveaccess', 'inversores.index');

        $inversor = Inversor::findOrFail($id);
        $movimientos = $inversor->movimientos()->with('cuenta')->orderByDesc('fecha')->orderByDesc('id')->get();

        $aportes = (float) $movimientos->where('tipo', 'aporte')->sum('monto');
        $retiros = (float) $movimientos->whereIn('tipo', ['retiro', 'distribucion'])->sum('monto');
        $saldo = round($aportes - $retiros, 2);

        return view('finanzas.inversores.ficha', compact('inversor', 'movimientos', 'aportes', 'retiros', 'saldo'));
    }

    /**
     * Registra un aporte o retiro. cuenta_ref es opcional ("caja-{aperturaId}"
     * o "banco-{cuentaId}"): si se indica, además mueve la tesorería real.
     */
    public function registrarMovimiento(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'inversores.movimiento');

        $request->validate([
            'tipo' => 'required|in:aporte,retiro',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'concepto' => 'nullable|string|max:250',
            'cuenta_ref' => 'nullable|string',
        ]);

        $inversor = Inversor::findOrFail($id);

        DB::beginTransaction();
        try {
            $movimientoId = null;
            if ($request->filled('cuenta_ref')) {
                $movimientoId = $this->crearMovimientoTesoreria(
                    $request->cuenta_ref,
                    $request->tipo === 'aporte' ? 'ingreso' : 'egreso',
                    (float) $request->monto,
                    'Inversor ' . $inversor->nombre . ($request->tipo === 'aporte' ? ' — aporte' : ' — retiro') . ($request->concepto ? ': ' . $request->concepto : '')
                );
            }

            InversorMovimiento::create([
                'inversor_id' => $inversor->id,
                'tipo' => $request->tipo,
                'monto' => $request->monto,
                'concepto' => $request->concepto,
                'fecha' => $request->fecha,
                'cuenta_id' => $movimientoId ? Movimiento::find($movimientoId)->cuenta_id : null,
                'movimiento_id' => $movimientoId,
                'user_id' => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['estado' => 1]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['estado' => 0, 'mensaje' => 'Error al registrar el movimiento: ' . $e->getMessage()], 422);
        }
    }

    /**
     * Reparto de ganancias: dado un monto total, le paga a cada inversor
     * activo con % > 0 su parte proporcional, cada una como un movimiento
     * tipo "distribucion". cuenta_ref es opcional, igual que arriba.
     */
    public function repartoGanancias(Request $request)
    {
        Gate::authorize('haveaccess', 'inversores.reparto');

        $request->validate([
            'monto_total' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'concepto' => 'nullable|string|max:250',
            'cuenta_ref' => 'nullable|string',
        ]);

        $inversores = Inversor::where('activo', true)
            ->where('porcentaje_participacion', '>', 0)
            ->get();

        if ($inversores->isEmpty()) {
            return response()->json(['estado' => 0, 'mensaje' => 'No hay inversores activos con % de participación cargado.'], 422);
        }

        $montoTotal = (float) $request->monto_total;
        $repartos = [];

        DB::beginTransaction();
        try {
            foreach ($inversores as $inversor) {
                $parte = round($montoTotal * ((float) $inversor->porcentaje_participacion / 100), 2);
                if ($parte <= 0) {
                    continue;
                }

                $movimientoId = null;
                if ($request->filled('cuenta_ref')) {
                    $movimientoId = $this->crearMovimientoTesoreria(
                        $request->cuenta_ref,
                        'egreso',
                        $parte,
                        'Inversor ' . $inversor->nombre . ' — reparto de ganancias (' . $inversor->porcentaje_participacion . '%)' . ($request->concepto ? ': ' . $request->concepto : '')
                    );
                }

                InversorMovimiento::create([
                    'inversor_id' => $inversor->id,
                    'tipo' => 'distribucion',
                    'monto' => $parte,
                    'concepto' => 'Reparto de ganancias' . ($request->concepto ? ': ' . $request->concepto : ''),
                    'fecha' => $request->fecha,
                    'cuenta_id' => $movimientoId ? Movimiento::find($movimientoId)->cuenta_id : null,
                    'movimiento_id' => $movimientoId,
                    'user_id' => Auth::id(),
                ]);

                $repartos[] = ['inversor' => $inversor->nombre, 'porcentaje' => $inversor->porcentaje_participacion, 'monto' => $parte];
            }

            DB::commit();
            return response()->json(['estado' => 1, 'repartos' => $repartos]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['estado' => 0, 'mensaje' => 'Error al repartir: ' . $e->getMessage()], 422);
        }
    }

    /** Crea el Movimiento de tesorería real y devuelve su id. */
    private function crearMovimientoTesoreria(string $cuentaRef, string $tipo, float $monto, string $observaciones): int
    {
        $cuentaId = null;
        $aperturaId = null;
        $efectivo = 0;
        $bancos = 0;

        if (str_starts_with($cuentaRef, 'caja-')) {
            $aperturaId = (int) str_replace('caja-', '', $cuentaRef);
            $apertura = CajaApertura::findOrFail($aperturaId);
            if (!$apertura->estaAbierta()) {
                throw new \RuntimeException('La caja seleccionada no está abierta.');
            }
            $cuentaId = $apertura->cuenta_id;
            $efectivo = $monto;
        } elseif (str_starts_with($cuentaRef, 'banco-')) {
            $cuentaId = (int) str_replace('banco-', '', $cuentaRef);
            $bancos = $monto;
        } else {
            throw new \RuntimeException('Cuenta inválida.');
        }

        $mov = Movimiento::create([
            'cuenta_id' => $cuentaId,
            'caja_apertura_id' => $aperturaId,
            'fecha' => now(),
            'tipo' => $tipo,
            'medio' => $efectivo > 0 ? 'efectivo' : 'transferencia',
            'cliente_proveedor' => 'Inversor',
            'observaciones' => $observaciones,
            'efectivo' => $efectivo,
            'bancos' => $bancos,
            'total' => $monto,
            'total_ars' => $monto,
        ]);

        return $mov->id;
    }
}
