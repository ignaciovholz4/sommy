<?php

namespace App\Http\Controllers\Cuentas;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\CajaApertura;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MovimientoController extends Controller
{
    /**
     * Mostrar vista de movimientos según tipo de cuenta.
     */
    public function index(Cuenta $cuenta, Request $request)
    {
        $aperturaId = $request->query('apertura');
        $apertura   = null;

        if ($cuenta->esCaja()) {
            if ($aperturaId) {
                $apertura = CajaApertura::find($aperturaId);
            } else {
                $apertura = CajaApertura::where('cuenta_id', $cuenta->id)
                    ->orderByDesc('fecha_apertura')
                    ->first();
            }
        }

        $estaAbierta = $apertura && $apertura->estaAbierta();

        // KPIs
        $dateNow   = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();

        if ($cuenta->esCaja() && $apertura) {
            $movsTodos   = Movimiento::where('caja_apertura_id', $apertura->id)->get();
            $saldoActual = $apertura->fondo_inicial
                + $movsTodos->where('tipo', 'ingreso')->sum('total')
                - $movsTodos->where('tipo', 'egreso')->sum('total');
        } else {
            $movsTodos   = Movimiento::where('cuenta_id', $cuenta->id)->get();
            $saldoActual = $movsTodos->where('tipo', 'ingreso')->sum('total')
                         - $movsTodos->where('tipo', 'egreso')->sum('total');
        }
        $saldoActual = round($saldoActual, 2);

        $movsMes     = Movimiento::where('cuenta_id', $cuenta->id)->where('fecha', '>=', $inicioMes)->get();
        $ingresosMes = round($movsMes->where('tipo', 'ingreso')->sum('total'), 2);
        $egresosMes  = round($movsMes->where('tipo', 'egreso')->sum('total'), 2);
        $efectivoMes = round($movsMes->sum('efectivo'), 2);
        $bancosMes   = round($movsMes->sum('bancos'), 2);
        $tarjetasMes = round($movsMes->sum('tarjetas'), 2);

        $movsHoy     = Movimiento::where('cuenta_id', $cuenta->id)->whereDate('fecha', $dateNow)->get();
        $ingresosHoy = round($movsHoy->where('tipo', 'ingreso')->sum('total'), 2);
        $egresosHoy  = round($movsHoy->where('tipo', 'egreso')->sum('total'), 2);

        return view('cuentas.movimientos.index', compact(
            'cuenta', 'aperturaId', 'estaAbierta',
            'saldoActual', 'ingresosMes', 'egresosMes',
            'ingresosHoy', 'egresosHoy',
            'efectivoMes', 'bancosMes', 'tarjetasMes'
        ));
    }

    /**
     * DataTable de movimientos según tipo de cuenta.
     */
    public function data(Cuenta $cuenta, Request $request)
    {
        if ($cuenta->esCaja()) {
            $query = Movimiento::where('cuenta_id', $cuenta->id);

            if ($request->has('apertura') && $request->apertura) {
                $query->where('caja_apertura_id', $request->apertura);
            }
        } else {
            // Banco: todos los movimientos vinculados a la cuenta
            $query = Movimiento::where('cuenta_id', $cuenta->id);
        }

        return datatables()->eloquent($query)
            ->addColumn('fecha_fmt', function($mov){
                return $mov->fecha
                    ? \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y H:i')
                    : '—';
            })
            ->addColumn('estado', function($mov){
                return $mov->tipo === 'ingreso'
                    ? '<span class="mov-chip mov-chip-ingreso"><i class="fas fa-arrow-up"></i> Ingreso</span>'
                    : '<span class="mov-chip mov-chip-egreso"><i class="fas fa-arrow-down"></i> Egreso</span>';
            })
            ->addColumn('medio', function($mov){
                $medios = [];
                if ($mov->efectivo > 0) $medios[] = '<span class="mov-chip mov-chip-medio"><i class="fas fa-money-bill-wave"></i> Efectivo</span>';
                if ($mov->bancos > 0)   $medios[] = '<span class="mov-chip mov-chip-medio"><i class="fas fa-university"></i> Banco</span>';
                if ($mov->tarjetas > 0) $medios[] = '<span class="mov-chip mov-chip-medio"><i class="fas fa-credit-card"></i> Tarjeta</span>';
                return $medios ? implode(' ', $medios) : '<span class="text-muted">—</span>';
            })
            ->addColumn('monto', function($mov){
                $signo = $mov->tipo === 'ingreso' ? '+' : '−';
                $clase = $mov->tipo === 'ingreso' ? 'mov-monto-ingreso' : 'mov-monto-egreso';
                return '<span class="'.$clase.'">'.$signo.'$'.number_format($mov->total, 2, ',', '.').'</span>';
            })
            ->addColumn('operacion_url', function($mov){
                // Link a la operación que originó el movimiento (venta, pedido, gasto, compra)
                $c = (string) $mov->comprobante;

                if (preg_match('/^Pedido #(\d+)/i', $c, $m)) {
                    return url('orders/order/' . $m[1]);
                }
                if (str_starts_with($c, 'VTA-')) {
                    $id = DB::table('ventas')->where('num_folio', $c)->value('idventa');
                    return $id ? url('ventas?ver=' . $id) : null;
                }
                if (preg_match('/^Gasto #(\d+)/i', $c)) {
                    return url('finanzas/gastos');
                }
                if (str_starts_with($c, 'CMP-') || preg_match('/Compra #\d+/i', $c)) {
                    return url('compras');
                }
                if (preg_match('/Flete envío #\d+/iu', $c . ' ' . (string) $mov->observaciones)) {
                    return url('envios');
                }

                return null;
            })
            ->addColumn('acciones', function($mov){
                return '<button class="btn btn-sm btn-outline-info btn-ver" data-id="'.$mov->id.'" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>';
            })
            ->rawColumns(['estado','medio','monto','acciones'])
            ->toJson();
    }

    /**
     * Registrar un nuevo movimiento.
     */
    public function store(Request $request, Cuenta $cuenta)
    {
        // Si es caja, validar apertura
        if ($cuenta->esCaja()) {
            $aperturaId = $request->input('apertura_id');
            $apertura   = $aperturaId ? CajaApertura::find($aperturaId) : null;

            if (!$apertura || !$apertura->estaAbierta()) {
                return response()->json([
                    'estado'  => 0,
                    'mensaje' => 'La caja está cerrada. No se pueden registrar movimientos.'
                ], 403);
            }
        }

        // Validación común (JS ya envía 0 en el campo que no corresponde)
        $validated = $request->validate([
            'tipo'             => 'required|in:ingreso,egreso',
            'cliente_proveedor'=> 'nullable|string|max:255',
            'comprobante'      => 'required|string|max:255',
            'observaciones'    => 'nullable|string',
            'efectivo'         => 'nullable|numeric|min:0',
            'bancos'           => 'nullable|numeric|min:0',
            'tarjetas'         => 'nullable|numeric|min:0',
            'total'            => 'required|numeric|min:0',
            'apertura_id'      => $cuenta->esCaja() ? 'required|exists:caja_aperturas,id' : 'nullable',
        ]);

        // Crear movimiento
        $movimiento = Movimiento::create([
            'cuenta_id'        => $cuenta->id,
            'caja_apertura_id' => $cuenta->esCaja() ? $validated['apertura_id'] : null,
            'fecha'            => now(),
            'tipo'             => $validated['tipo'],
            'cliente_proveedor'=> $validated['cliente_proveedor'] ?? null,
            'comprobante'      => $validated['comprobante'],
            'observaciones'    => $validated['observaciones'] ?? null,
            'efectivo'         => $validated['efectivo'] ?? 0,
            'bancos'           => $validated['bancos'] ?? 0,
            'tarjetas'         => $validated['tarjetas'] ?? 0,
            'total'            => $validated['total'],
        ]);

        return response()->json([
            'estado'     => 1,
            'mensaje'    => 'Movimiento registrado correctamente.',
            'movimiento' => $movimiento,
        ]);
    }

    /**
     * Detalle completo de un movimiento, resolviendo su comprobante de origen.
     */
    public function detalle(Cuenta $cuenta, Movimiento $movimiento)
    {
        $comp        = $movimiento->comprobante ?? '';
        $obs         = mb_strtolower($movimiento->observaciones ?? '');
        $esDevolucion = str_contains($obs, 'devoluci');

        // — Transferencia —
        if (str_starts_with($comp, 'TRF-')) {
            return response()->json([
                'estado'     => 1,
                'tipo'       => 'transferencia',
                'movimiento' => $this->_movData($movimiento),
            ]);
        }

        // — Manual (MRC / MPC) —
        if (str_starts_with($comp, 'MRC-') || str_starts_with($comp, 'MPC-')) {
            return response()->json([
                'estado'     => 1,
                'tipo'       => 'manual',
                'movimiento' => $this->_movData($movimiento),
            ]);
        }

        // — Ecommerce: "Pedido #N" —
        if (str_starts_with($comp, 'Pedido #')) {
            $orderId = (int) str_replace('Pedido #', '', $comp);
            $order   = \App\Models\ecommerce\order_ecommerce::with([
                'cliente', 'status',
                'detalles.producto',
                'pago',
            ])->find($orderId);

            return response()->json([
                'estado'     => 1,
                'tipo'       => 'ecommerce',
                'movimiento' => $this->_movData($movimiento),
                'link'       => $order ? url('/orders/order/' . $order->order_id) : null,
                'orden'      => $order ? [
                    'order_id'       => $order->order_id,
                    'fecha'          => $order->order_date,
                    'estado'         => optional($order->status)->status_name ?? '—',
                    'cliente'        => optional($order->cliente)->nombre ?? '—',
                    'subtotal'       => $order->subtotal_amount,
                    'total'          => $order->total_amount,
                    'detalles'       => $order->detalles->map(fn($d) => [
                        'articulo'   => optional($d->producto)->nombre ?? '—',
                        'cantidad'   => $d->quantity ?? 1,
                        'precio'     => $d->price ?? 0,
                        'subtotal'   => $d->total ?? (($d->price ?? 0) * ($d->quantity ?? 1)),
                    ])->toArray(),
                ] : null,
            ]);
        }

        // — Buscar en Ventas —
        $venta = \App\Models\Venta::with([
            'cliente', 'user', 'tipoComprobante', 'sucursal',
            'detalles.articulo', 'detalles.combinacion',
        ])->where('num_folio', $comp)->first();

        if ($venta) {
            return response()->json([
                'estado'      => 1,
                'tipo'        => $esDevolucion ? 'devolucion_venta' : 'venta',
                'movimiento'  => $this->_movData($movimiento),
                'link'        => url('/ventas?ver=' . $venta->idventa),
                'comprobante' => $this->_ventaData($venta),
            ]);
        }

        // — Buscar en Compras —
        $compra = \App\Models\Compra::with([
            'proveedor', 'user', 'tipoComprobante', 'sucursal',
            'detalles.articulo', 'detalles.combinacion',
        ])->where('num_folio', $comp)->first();

        if ($compra) {
            return response()->json([
                'estado'      => 1,
                'tipo'        => $esDevolucion ? 'devolucion_compra' : 'compra',
                'movimiento'  => $this->_movData($movimiento),
                'link'        => url('/compras?ver=' . $compra->idcompra),
                'comprobante' => $this->_compraData($compra),
            ]);
        }

        // — Fallback: manual —
        return response()->json([
            'estado'     => 1,
            'tipo'       => 'manual',
            'movimiento' => $this->_movData($movimiento),
        ]);
    }

    private function _movData(Movimiento $m): array
    {
        return [
            'id'                => $m->id,
            'fecha'             => $m->fecha ? \Carbon\Carbon::parse($m->fecha)->format('d/m/Y H:i') : null,
            'tipo'              => $m->tipo,
            'cliente_proveedor' => $m->cliente_proveedor,
            'comprobante'       => $m->comprobante,
            'observaciones'     => $m->observaciones,
            'efectivo'          => (float) $m->efectivo,
            'bancos'            => (float) $m->bancos,
            'tarjetas'          => (float) $m->tarjetas,
            'total'             => (float) $m->total,
        ];
    }

    private function _ventaData(\App\Models\Venta $v): array
    {
        return [
            'id'               => $v->idventa,
            'num_folio'        => $v->num_folio,
            'fecha'            => $v->fecha,
            'estado'           => $v->estado,
            'tipo_comprobante' => optional($v->tipoComprobante)->descripcion ?? '—',
            'contraparte'      => optional($v->cliente)->nombre ?? '—',
            'usuario'          => optional($v->user)->name ?? '—',
            'sucursal'         => optional($v->sucursal)->nombre ?? '—',
            'total_neto'       => (float) $v->total_neto,
            'total_con_iva'    => (float) $v->total_con_iva,
            'iva_discriminado' => $v->iva_discriminado,
            'detalles'         => $v->detalles->map(fn($d) => [
                'articulo'         => optional($d->articulo)->nombre ?? '—',
                'combinacion'      => optional($d->combinacion)->combinacion,
                'cantidad'         => (float) $d->cantidad,
                'precio_unitario'  => (float) $d->precio_unitario,
                'descuento'        => (float) $d->descuento,
                'iva'              => (float) $d->iva,
                'subtotal_neto'    => (float) $d->subtotal_neto,
                'subtotal_con_iva' => (float) $d->subtotal_con_iva,
            ])->toArray(),
        ];
    }

    private function _compraData(\App\Models\Compra $c): array
    {
        return [
            'id'               => $c->idcompra,
            'num_folio'        => $c->num_folio,
            'fecha'            => $c->fecha,
            'estado'           => $c->estado,
            'tipo_comprobante' => optional($c->tipoComprobante)->descripcion ?? '—',
            'contraparte'      => optional($c->proveedor)->nombre ?? '—',
            'usuario'          => optional($c->user)->name ?? '—',
            'sucursal'         => optional($c->sucursal)->nombre ?? '—',
            'total_neto'       => (float) $c->total_neto,
            'total_con_iva'    => (float) $c->total_con_iva,
            'iva_discriminado' => $c->iva_discriminado,
            'detalles'         => $c->detalles->map(fn($d) => [
                'articulo'         => optional($d->articulo)->nombre ?? '—',
                'combinacion'      => optional($d->combinacion)->combinacion,
                'cantidad'         => (float) $d->cantidad,
                'precio_unitario'  => (float) $d->precio_unitario,
                'descuento'        => (float) $d->descuento,
                'iva'              => (float) $d->iva,
                'subtotal_neto'    => (float) $d->subtotal_neto,
                'subtotal_con_iva' => (float) $d->subtotal_con_iva,
            ])->toArray(),
        ];
    }

    public function transferir(Request $request)
    {
        $request->validate([
            'origen_id'   => 'required|string', // puede venir "caja-12" o "banco-5"
            'destino_id'  => 'required|string',
            'monto'       => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string',
        ]);

        // Validar que origen y destino sean distintos
        if ($request->origen_id === $request->destino_id) {
            return response()->json([
                'success' => false,
                'error'   => 'La cuenta origen y destino deben ser diferentes.'
            ]);
        }

        DB::beginTransaction();
        try {
            $monto = $request->monto;
            $observaciones = $request->observaciones ?? 'Transferencia entre cuentas';

            // 🔹 Procesar cuenta origen
            $origenCuentaId   = null;
            $origenAperturaId = null;

            if (str_starts_with($request->origen_id, 'caja-')) {
                $aperturaId = (int) str_replace('caja-', '', $request->origen_id);
                $apertura   = CajaApertura::findOrFail($aperturaId);

                if (!$apertura->estaAbierta()) {
                    return response()->json(['success' => false, 'error' => 'La caja origen no está abierta.']);
                }

                $origenCuentaId   = $apertura->cuenta_id;
                $origenAperturaId = $apertura->id;
            } elseif (str_starts_with($request->origen_id, 'banco-')) {
                $origenCuentaId = (int) str_replace('banco-', '', $request->origen_id);
            }

            Movimiento::create([
                'cuenta_id'        => $origenCuentaId,
                'caja_apertura_id' => $origenAperturaId,
                'fecha'            => now(),
                'tipo'             => 'egreso',
                'cliente_proveedor'=> 'Transferencia',
                'comprobante'      => 'TRF-' . uniqid(),
                'observaciones'    => $observaciones,
                'efectivo'         => str_starts_with($request->origen_id, 'caja-') ? $monto : 0,
                'bancos'           => str_starts_with($request->origen_id, 'banco-') ? $monto : 0,
                'tarjetas'         => 0,
                'total'            => $monto,
            ]);

            // 🔹 Procesar cuenta destino
            $destinoCuentaId   = null;
            $destinoAperturaId = null;

            if (str_starts_with($request->destino_id, 'caja-')) {
                $aperturaId = (int) str_replace('caja-', '', $request->destino_id);
                $apertura   = CajaApertura::findOrFail($aperturaId);

                if (!$apertura->estaAbierta()) {
                    return response()->json(['success' => false, 'error' => 'La caja destino no está abierta.']);
                }

                $destinoCuentaId   = $apertura->cuenta_id;
                $destinoAperturaId = $apertura->id;
            } elseif (str_starts_with($request->destino_id, 'banco-')) {
                $destinoCuentaId = (int) str_replace('banco-', '', $request->destino_id);
            }

            Movimiento::create([
                'cuenta_id'        => $destinoCuentaId,
                'caja_apertura_id' => $destinoAperturaId,
                'fecha'            => now(),
                'tipo'             => 'ingreso',
                'cliente_proveedor'=> 'Transferencia',
                'comprobante'      => 'TRF-' . uniqid(),
                'observaciones'    => $observaciones,
                'efectivo'         => str_starts_with($request->destino_id, 'caja-') ? $monto : 0,
                'bancos'           => str_starts_with($request->destino_id, 'banco-') ? $monto : 0,
                'tarjetas'         => 0,
                'total'            => $monto,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transferencia realizada correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Error en la transferencia: '.$e->getMessage()]);
        }
    }

}