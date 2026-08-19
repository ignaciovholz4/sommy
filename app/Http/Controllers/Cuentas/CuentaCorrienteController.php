<?php

namespace App\Http\Controllers\Cuentas;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Cuenta corriente de clientes: cargos (ventas a cuenta, señas pendientes)
 * y pagos (recibos). El saldo del cliente = cargos - pagos.
 */
class CuentaCorrienteController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('haveaccess', 'ventas.index');

        $q = trim($request->input('q', ''));

        $saldos = DB::table('clientes as c')
            ->leftJoin('cliente_cc_movimientos as m', 'm.cliente_id', '=', 'c.idcliente')
            ->groupBy('c.idcliente', 'c.nombre', 'c.paterno', 'c.materno', 'c.telefono', 'c.email')
            ->selectRaw("c.idcliente, c.nombre, c.paterno, c.materno, c.telefono, c.email,
                COALESCE(SUM(CASE WHEN m.tipo = 'cargo' THEN m.monto ELSE 0 END), 0) as cargos,
                COALESCE(SUM(CASE WHEN m.tipo = 'pago' THEN m.monto ELSE 0 END), 0) as pagos");

        if ($q !== '') {
            $saldos->where(function ($w) use ($q) {
                $w->where('c.nombre', 'like', "%$q%")
                  ->orWhere('c.paterno', 'like', "%$q%")
                  ->orWhere('c.email', 'like', "%$q%")
                  ->orWhere('c.telefono', 'like', "%$q%")
                  ->orWhere('c.dni_cuit', 'like', "%$q%");
            });
        }

        // Deuda por ventas "a cobrar" (total - cobrado por caja/banco), por cliente
        $ventasPend = DB::table('ventas as v')
            ->leftJoin(DB::raw('(SELECT comprobante, SUM(total) as cobrado FROM movimientos GROUP BY comprobante) mv'), 'mv.comprobante', '=', 'v.num_folio')
            ->where('v.estado', 'a cobrar')
            ->groupBy('v.cliente_id')
            ->selectRaw('v.cliente_id, SUM(v.total_con_iva - COALESCE(mv.cobrado, 0)) as pendiente')
            ->get()->keyBy('cliente_id');

        $clientes = $saldos->get()->map(function ($c) use ($ventasPend) {
            $c->ventas_pendiente = (float) optional($ventasPend->get($c->idcliente))->pendiente ?: 0;
            $c->saldo = (float) $c->cargos - (float) $c->pagos;
            $c->saldo_total = $c->saldo + $c->ventas_pendiente;
            return $c;
        });

        // Con búsqueda se muestran todos los resultados; sin búsqueda, solo los que tienen deuda o movimientos
        if ($q === '') {
            $clientes = $clientes->filter(fn ($c) => $c->cargos > 0 || $c->pagos > 0 || $c->ventas_pendiente > 0)->values();
        }

        $clientes = $clientes->sortByDesc('saldo_total')->values();
        $totalDeuda = $clientes->where('saldo_total', '>', 0)->sum('saldo_total');

        return view('cuentas.cc.index', compact('clientes', 'totalDeuda', 'q'));
    }

    public function cliente($id)
    {
        Gate::authorize('haveaccess', 'ventas.index');

        $cliente = Cliente::findOrFail($id);

        $movimientos = DB::table('cliente_cc_movimientos')
            ->where('cliente_id', $id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $cargos = $movimientos->where('tipo', 'cargo')->sum('monto');
        $pagos  = $movimientos->where('tipo', 'pago')->sum('monto');
        $saldo  = $cargos - $pagos;

        // Ventas a cobrar del cliente: total, cobrado hasta ahora y lo que falta
        $ventasACobrar = \App\Models\Venta::with('movimientos.cuenta')
            ->where('cliente_id', $id)
            ->where('estado', 'a cobrar')
            ->orderByDesc('fecha')->get()
            ->map(function ($v) {
                $v->cobrado = (float) $v->movimientos->sum('total');
                $v->pendiente = max((float) $v->total_con_iva - $v->cobrado, 0);
                return $v;
            });
        $deudaVentas = (float) $ventasACobrar->sum('pendiente');

        return view('cuentas.cc.cliente', compact('cliente', 'movimientos', 'saldo', 'cargos', 'pagos', 'ventasACobrar', 'deudaVentas'));
    }

    public function storeMovimiento(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'ventas.index');

        $request->validate([
            'tipo'       => 'required|in:cargo,pago',
            'monto'      => 'required|numeric|min:0.01',
            'concepto'   => 'required|string|max:200',
            'medio_pago' => 'nullable|string|max:30',
            'referencia' => 'nullable|string|max:60',
        ], [
            'monto.required'    => 'Ingresá el monto.',
            'monto.min'         => 'El monto debe ser mayor a cero.',
            'concepto.required' => 'Ingresá un concepto (ej: "Venta colchón Serena" o "Recibo seña").',
        ]);

        Cliente::findOrFail($id);

        DB::table('cliente_cc_movimientos')->insert([
            'cliente_id' => $id,
            'tipo'       => $request->tipo,
            'concepto'   => $request->concepto,
            'monto'      => $request->monto,
            'medio_pago' => $request->tipo === 'pago' ? ($request->medio_pago ?: 'efectivo') : null,
            'referencia' => $request->referencia,
            'user_id'    => Auth::id(),
        ]);

        return back()->with('cc_ok', $request->tipo === 'pago' ? 'Pago registrado correctamente.' : 'Cargo registrado correctamente.');
    }
}
