<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\CajaApertura;
use App\Models\Movimiento;
use App\Models\Proveedor;
use App\Models\ProveedorCcMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Cuentas por pagar: cuenta corriente de proveedores.
 * Espejo de la cuenta corriente de clientes, con vencimientos e imputación FIFO.
 */
class CuentaCorrienteProveedorController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.cxp.index');

        $q   = trim($request->input('q', ''));
        $hoy = now()->toDateString();

        $saldos = DB::table('proveedores as p')
            ->leftJoin('proveedor_cc_movimientos as m', 'm.proveedor_id', '=', 'p.idproveedor')
            ->groupBy('p.idproveedor', 'p.nombre', 'p.telefono', 'p.email', 'p.condicion_pago_dias')
            ->selectRaw("p.idproveedor, p.nombre, p.telefono, p.email, p.condicion_pago_dias,
                COALESCE(SUM(CASE WHEN m.tipo = 'debe' THEN m.monto ELSE 0 END), 0) as debe,
                COALESCE(SUM(CASE WHEN m.tipo = 'haber' THEN m.monto ELSE 0 END), 0) as haber,
                COALESCE(SUM(CASE WHEN m.tipo = 'debe' AND m.estado != 'pagado' AND m.fecha_vencimiento IS NOT NULL AND m.fecha_vencimiento < ? THEN m.monto ELSE 0 END), 0) as vencido,
                COALESCE(SUM(CASE WHEN m.tipo = 'debe' AND m.estado != 'pagado' AND (m.fecha_vencimiento IS NULL OR m.fecha_vencimiento >= ?) THEN m.monto ELSE 0 END), 0) as por_vencer",
                [$hoy, $hoy]);

        if ($q !== '') {
            $saldos->where(function ($w) use ($q) {
                $w->where('p.nombre', 'like', "%$q%")
                  ->orWhere('p.email', 'like', "%$q%")
                  ->orWhere('p.telefono', 'like', "%$q%");
            });
        }

        $proveedores = $saldos->get()->map(function ($p) {
            $p->saldo = round((float) $p->debe - (float) $p->haber, 2);
            return $p;
        });

        // Sin búsqueda mostramos solo proveedores con movimientos; con búsqueda, todos
        if ($q === '') {
            $proveedores = $proveedores->filter(fn ($p) => $p->debe > 0 || $p->haber > 0)->values();
        }

        $proveedores = $proveedores->sortByDesc('saldo')->values();
        $totalDeuda  = $proveedores->where('saldo', '>', 0)->sum('saldo');
        $totalVencido = $proveedores->sum('vencido');

        return view('finanzas.cxp.index', compact('proveedores', 'totalDeuda', 'totalVencido', 'q'));
    }

    public function show($proveedorId)
    {
        Gate::authorize('haveaccess', 'finanzas.cxp.index');

        $proveedor = Proveedor::findOrFail($proveedorId);

        $movimientos = ProveedorCcMovimiento::with(['compra', 'movimiento.cuenta'])
            ->where('proveedor_id', $proveedorId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $debe  = (float) $movimientos->where('tipo', 'debe')->sum('monto');
        $haber = (float) $movimientos->where('tipo', 'haber')->sum('monto');
        $saldo = round($debe - $haber, 2);

        // Deudas pendientes en orden FIFO (las mismas que va a cancelar el próximo pago)
        $deudasPendientes = ProveedorCcMovimiento::with('compra')
            ->where('proveedor_id', $proveedorId)
            ->where('tipo', 'debe')
            ->where('estado', '!=', 'pagado')
            ->orderByRaw('fecha_vencimiento IS NULL, fecha_vencimiento asc, id asc')
            ->get();

        return view('finanzas.cxp.show', compact('proveedor', 'movimientos', 'debe', 'haber', 'saldo', 'deudasPendientes'));
    }

    /**
     * Registra un pago al proveedor: egreso en tesorería + fila haber en la CC
     * + reimputación FIFO de las deudas.
     * La cuenta llega como "caja-{aperturaId}" o "banco-{cuentaId}".
     */
    public function registrarPago(Request $request, $proveedorId)
    {
        Gate::authorize('haveaccess', 'finanzas.cxp.manage');

        $request->validate([
            'monto'       => 'required|numeric|min:0.01',
            'cuenta'      => 'required|string',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'monto.required'  => 'Ingresá el monto del pago.',
            'monto.min'       => 'El monto debe ser mayor a cero.',
            'cuenta.required' => 'Seleccioná la cuenta desde la que se paga.',
        ]);

        $proveedor = Proveedor::findOrFail($proveedorId);

        $saldo = $proveedor->saldoCc();
        if ((float) $request->monto > $saldo + 0.009) {
            return response()->json(['estado' => 0, 'mensaje' => 'El monto supera el saldo adeudado ($' . number_format($saldo, 2, ',', '.') . ').']);
        }

        DB::beginTransaction();
        try {
            $monto      = (float) $request->monto;
            $cuentaId   = null;
            $aperturaId = null;
            $efectivo   = 0;
            $bancos     = 0;

            if (str_starts_with($request->cuenta, 'caja-')) {
                $aperturaId = (int) str_replace('caja-', '', $request->cuenta);
                $apertura   = CajaApertura::findOrFail($aperturaId);

                if (!$apertura->estaAbierta()) {
                    DB::rollBack();

                    return response()->json(['estado' => 0, 'mensaje' => 'La caja seleccionada no está abierta.']);
                }

                $cuentaId = $apertura->cuenta_id;
                $efectivo = $monto;
            } elseif (str_starts_with($request->cuenta, 'banco-')) {
                $cuentaId = (int) str_replace('banco-', '', $request->cuenta);
                $bancos   = $monto;
            } else {
                DB::rollBack();

                return response()->json(['estado' => 0, 'mensaje' => 'Formato de cuenta inválido.']);
            }

            // Fila haber en la cuenta corriente del proveedor
            $ccMovimiento = ProveedorCcMovimiento::create([
                'proveedor_id' => $proveedor->idproveedor,
                'tipo'         => 'haber',
                'origen'       => 'pago',
                'monto'        => $monto,
                'descripcion'  => $request->descripcion ?: 'Pago a proveedor',
                'user_id'      => auth()->id(),
            ]);

            // Egreso en tesorería referenciando el movimiento de CC
            $movimiento = Movimiento::create([
                'cuenta_id'         => $cuentaId,
                'caja_apertura_id'  => $aperturaId,
                'fecha'             => now(),
                'tipo'              => 'egreso',
                'cliente_proveedor' => $proveedor->nombre,
                'comprobante'       => 'Pago CxP #' . $ccMovimiento->id,
                'observaciones'     => $request->descripcion ?: 'Pago de cuenta corriente a proveedor',
                'efectivo'          => $efectivo,
                'bancos'            => $bancos,
                'tarjetas'          => 0,
                'total'             => $monto,
                'referencia_type'   => ProveedorCcMovimiento::class,
                'referencia_id'     => $ccMovimiento->id,
            ]);

            $ccMovimiento->update(['movimiento_id' => $movimiento->id]);

            // Imputación FIFO contra las deudas pendientes
            ProveedorCcMovimiento::reimputarFifo((int) $proveedor->idproveedor);

            DB::commit();

            return response()->json(['estado' => 1, 'mensaje' => 'Pago registrado correctamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['estado' => 0, 'mensaje' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Ajuste manual de la cuenta corriente (debe o haber).
     */
    public function registrarAjuste(Request $request, $proveedorId)
    {
        Gate::authorize('haveaccess', 'finanzas.cxp.manage');

        $request->validate([
            'tipo'              => 'required|in:debe,haber',
            'origen'            => 'required|in:ajuste,nota_credito',
            'monto'             => 'required|numeric|min:0.01',
            'descripcion'       => 'required|string|max:255',
            'fecha_vencimiento' => 'nullable|date',
        ], [
            'monto.required'       => 'Ingresá el monto del ajuste.',
            'descripcion.required' => 'Ingresá una descripción del ajuste.',
        ]);

        $proveedor = Proveedor::findOrFail($proveedorId);

        DB::beginTransaction();
        try {
            ProveedorCcMovimiento::create([
                'proveedor_id'      => $proveedor->idproveedor,
                'tipo'              => $request->tipo,
                'origen'            => $request->origen,
                'monto'             => $request->monto,
                'fecha_vencimiento' => $request->tipo === 'debe' ? $request->fecha_vencimiento : null,
                'descripcion'       => $request->descripcion,
                'user_id'           => auth()->id(),
            ]);

            ProveedorCcMovimiento::reimputarFifo((int) $proveedor->idproveedor);

            DB::commit();

            return response()->json(['estado' => 1, 'mensaje' => 'Ajuste registrado correctamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['estado' => 0, 'mensaje' => 'Error al registrar el ajuste: ' . $e->getMessage()]);
        }
    }
}
