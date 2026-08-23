<?php

namespace App\Http\Controllers\Devolucion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Compra;
use App\Models\Venta;
use App\Models\CajaApertura;
use App\Models\Cuenta;
use App\Models\Movimiento;
use App\Models\Devolucion;
use App\Http\Controllers\StockController;

class DevolucionController extends Controller
{
    public function index()
    {
        // Historial de devoluciones con su referencia resuelta (venta, compra o pedido)
        $devoluciones = Devolucion::orderByDesc('fecha')->limit(50)->get()->map(function ($dev) {
            $ref = ['folio' => '—', 'persona' => '—', 'link' => null];

            if ($dev->tipo === 'venta') {
                $venta = Venta::with('cliente')->find($dev->referencia_id);
                if ($venta) {
                    $ref = [
                        'folio'   => $venta->num_folio ?: 'Venta #' . $venta->idventa,
                        'persona' => trim(optional($venta->cliente)->nombre . ' ' . optional($venta->cliente)->paterno) ?: '—',
                        'link'    => url('ventas?ver=' . $venta->idventa),
                    ];
                }
            } elseif ($dev->tipo === 'compra') {
                $compra = Compra::with('proveedor')->find($dev->referencia_id);
                if ($compra) {
                    $ref = [
                        'folio'   => $compra->num_folio ?: 'Compra #' . $compra->idcompra,
                        'persona' => optional($compra->proveedor)->nombre ?: '—',
                        'link'    => url('compras?ver=' . $compra->idcompra),
                    ];
                }
            } else {
                $order = \App\Models\ecommerce\order_ecommerce::with('cliente')->find($dev->referencia_id);
                if ($order) {
                    $ref = [
                        'folio'   => 'Pedido #' . $order->order_id,
                        'persona' => optional($order->cliente)->nombre ?: '—',
                        'link'    => url('orders/order/' . $order->order_id),
                    ];
                }
            }

            $dev->ref = $ref;
            return $dev;
        });

        // Operaciones sobre las que se puede registrar una devolución
        $ventasDevolvibles = Venta::with('cliente')
            ->where('estado', '!=', 'anulada')
            ->where('fecha', '>=', now()->subDays(60))
            ->orderByDesc('fecha')->orderByDesc('idventa')
            ->limit(40)->get();

        $pedidosDevolvibles = \App\Models\ecommerce\order_ecommerce::with(['cliente', 'status'])
            ->where('active', 1)
            ->where('status_order_id', '!=', 6)
            ->where('order_date', '>=', now()->subDays(60))
            ->orderByDesc('order_date')
            ->limit(40)->get();

        return view('devolucion.index', compact('devoluciones', 'ventasDevolvibles', 'pedidosDevolvibles'));
    }

    /**
     * Anula/devuelve un pedido multicanal: revierte la plata cobrada
     * (contramovimientos), repone stock si ya se había descontado,
     * anula la comisión del revendedor y deja el pedido en Cancelado.
     */
    public function anularPedido(Request $request, $orderId)
    {
        DB::beginTransaction();
        try {
            $order = \App\Models\ecommerce\order_ecommerce::with(['cliente', 'asignaciones'])->findOrFail($orderId);

            if ((int) $order->status_order_id === 6) {
                return response()->json(['success' => false, 'error' => 'El pedido ya está cancelado.']);
            }

            $comprobante = 'Pedido #' . $order->order_id;
            $ingresos = Movimiento::with(['cuenta', 'cajaApertura'])
                ->where('comprobante', $comprobante)
                ->where('tipo', 'ingreso')
                ->get();

            $pendientes = 0;

            foreach ($ingresos as $mov) {
                $cuenta = $mov->cuenta;

                if ($cuenta && $cuenta->tipo === 'caja') {
                    $apertura = $mov->cajaApertura;
                    if ($apertura && $apertura->estaAbierta()) {
                        Movimiento::create([
                            'cuenta_id'        => $cuenta->id,
                            'caja_apertura_id' => $apertura->id,
                            'fecha'            => now(),
                            'tipo'             => 'egreso',
                            'cliente_proveedor'=> optional($order->cliente)->nombre ?? 'Cliente ecommerce',
                            'comprobante'      => $comprobante,
                            'observaciones'    => 'Devolución de pedido',
                            'efectivo'         => $mov->efectivo,
                            'bancos'           => $mov->bancos,
                            'tarjetas'         => $mov->tarjetas,
                            'total'            => $mov->total,
                        ]);
                    } else {
                        $pendientes += (float) $mov->total;
                    }
                } elseif ($cuenta) {
                    Movimiento::create([
                        'cuenta_id'        => $cuenta->id,
                        'caja_apertura_id' => null,
                        'fecha'            => now(),
                        'tipo'             => 'egreso',
                        'cliente_proveedor'=> optional($order->cliente)->nombre ?? 'Cliente ecommerce',
                        'comprobante'      => $comprobante,
                        'observaciones'    => 'Devolución de pedido (Banco)',
                        'efectivo'         => 0,
                        'bancos'           => $mov->bancos,
                        'tarjetas'         => $mov->tarjetas,
                        'total'            => $mov->total,
                    ]);
                }
            }

            // Cobros hechos en cajas hoy cerradas: hay que devolverlos desde otra cuenta
            if ($pendientes > 0) {
                $cuentaId = $request->input('cuenta_id');
                if (!$cuentaId) {
                    $cajasAbiertas = CajaApertura::with('cuenta')
                        ->where('abierta', true)->whereNull('fecha_cierre')
                        ->get()
                        ->map(fn ($a) => [
                            'id'             => 'caja-' . $a->id,
                            'nombre'         => $a->cuenta->nombre ?? 'Caja',
                            'tipo'           => 'caja',
                            'fecha_apertura' => $a->fecha_apertura?->format('d/m/Y H:i'),
                        ])->values();

                    $bancos = Cuenta::where('tipo', 'banco')->where('activa', 1)->get()
                        ->map(fn ($c) => ['id' => 'banco-' . $c->id, 'nombre' => $c->nombre, 'tipo' => 'banco'])
                        ->values();

                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error'   => 'Seleccione una cuenta para devolver los cobros hechos en cajas ya cerradas.',
                        'cuentas' => $cajasAbiertas->concat($bancos)->all(),
                    ]);
                }

                if (str_starts_with($cuentaId, 'caja-')) {
                    $apertura = CajaApertura::findOrFail((int) str_replace('caja-', '', $cuentaId));
                    if (!$apertura->estaAbierta()) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'error' => 'La caja seleccionada no está abierta.']);
                    }
                    Movimiento::create([
                        'cuenta_id'        => $apertura->cuenta_id,
                        'caja_apertura_id' => $apertura->id,
                        'fecha'            => now(),
                        'tipo'             => 'egreso',
                        'cliente_proveedor'=> optional($order->cliente)->nombre ?? 'Cliente ecommerce',
                        'comprobante'      => $comprobante,
                        'observaciones'    => 'Devolución de pedido (consolidada)',
                        'efectivo'         => $pendientes,
                        'bancos'           => 0,
                        'tarjetas'         => 0,
                        'total'            => $pendientes,
                    ]);
                } elseif (str_starts_with($cuentaId, 'banco-')) {
                    $cuenta = Cuenta::findOrFail((int) str_replace('banco-', '', $cuentaId));
                    Movimiento::create([
                        'cuenta_id'        => $cuenta->id,
                        'caja_apertura_id' => null,
                        'fecha'            => now(),
                        'tipo'             => 'egreso',
                        'cliente_proveedor'=> optional($order->cliente)->nombre ?? 'Cliente ecommerce',
                        'comprobante'      => $comprobante,
                        'observaciones'    => 'Devolución de pedido (consolidada, Banco)',
                        'efectivo'         => 0,
                        'bancos'           => $pendientes,
                        'tarjetas'         => 0,
                        'total'            => $pendientes,
                    ]);
                }
            }

            // Stock: si el pedido ya se pagó/envió/entregó, el stock se descontó al pagar → se repone
            if (in_array((int) $order->status_order_id, [3, 4, 5])) {
                foreach ($order->asignaciones as $asig) {
                    app(StockController::class)->incrementarStockEnSucursal(
                        $asig->sucursal_id,
                        $asig->product_id,
                        $asig->cantidad,
                        $asig->combinacion_id
                    );
                }
            }
            \App\Models\ecommerce\order_stock_asignacion::where('order_id', $order->order_id)->delete();

            // Comisión de revendedor no pagada: se anula con el pedido
            \App\Models\RevendedorComision::where('order_id', $order->order_id)
                ->whereIn('estado', ['pendiente', 'aprobada'])
                ->update(['estado' => 'anulada']);

            $cobrado = (float) $ingresos->sum('total');
            Devolucion::create([
                'tipo'          => 'pedido',
                'referencia_id' => $order->order_id,
                'motivo'        => $request->input('motivo') ?: 'Devolución/anulación de pedido',
                'fecha'         => now(),
                'monto'         => $cobrado > 0 ? $cobrado : $order->total_amount,
            ]);

            $order->update(['status_order_id' => 6]);

            DB::commit();

            \App\Models\Notificacion::avisar('devolucion',
                'Devolución: pedido #' . $order->order_id . ' anulado',
                'El stock asignado se liberó y el cobro se revirtió si existía.',
                url('orders/order/' . $order->order_id), 'alerta');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function list(Request $request)
    {
        $devoluciones = Devolucion::query();

        return DataTables::of($devoluciones)
            ->addColumn('tipo', fn($dev) => ucfirst($dev->tipo))
            ->addColumn('referencia', function ($dev) {
                if ($dev->tipo === 'venta') {
                    $venta = Venta::with('sucursal','cliente')->find($dev->referencia_id);
                    return $venta ? 'Venta Folio: '.$venta->num_folio : '-';
                } else {
                    $compra = Compra::with('sucursal','proveedor')->find($dev->referencia_id);
                    return $compra ? 'Compra Folio: '.$compra->num_folio : '-';
                }
            })
            ->addColumn('persona', function ($dev) {
                if ($dev->tipo === 'venta') {
                    $venta = Venta::with('cliente')->find($dev->referencia_id);
                    return $venta && $venta->cliente ? $venta->cliente->nombre : '-';
                } else {
                    $compra = Compra::with('proveedor')->find($dev->referencia_id);
                    return $compra && $compra->proveedor ? $compra->proveedor->nombre : '-';
                }
            })
            ->addColumn('sucursal', function ($dev) {
                if ($dev->tipo === 'venta') {
                    $venta = Venta::with('sucursal')->find($dev->referencia_id);
                    return $venta && $venta->sucursal ? $venta->sucursal->nombre : '-';
                } else {
                    $compra = Compra::with('sucursal')->find($dev->referencia_id);
                    return $compra && $compra->sucursal ? $compra->sucursal->nombre : '-';
                }
            })
            ->editColumn('fecha', fn($dev) => \Carbon\Carbon::parse($dev->fecha)->format('d/m/Y H:i'))
            ->editColumn('monto', fn($dev) => number_format($dev->monto, 2))
            ->addColumn('action', function ($dev) {
                return '
                    <button class="btn btn-sm btn-info" onclick="getDetailDevolucion('.$dev->id.')">
                        <i class="fa fa-eye"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function buscarFolio(Request $request)
    {
        $folio = $request->input('folio');

        // Buscar primero en ventas
        $venta = Venta::where('num_folio', $folio)->first();
        if ($venta) {
            $devolucion = Devolucion::where('tipo', 'venta')
                ->where('referencia_id', $venta->idventa)
                ->first();

            if ($devolucion) {
                return response()->json([
                    'success' => true,
                    'iddevolucion' => $devolucion->id
                ]);
            }

            return response()->json([
                'success' => false,
                'error'   => 'No existe devolución asociada a esa venta.'
            ]);
        }

        // Buscar en compras
        $compra = Compra::where('num_folio', $folio)->first();
        if ($compra) {
            $devolucion = Devolucion::where('tipo', 'compra')
                ->where('referencia_id', $compra->idcompra)
                ->first();

            if ($devolucion) {
                return response()->json([
                    'success' => true,
                    'iddevolucion' => $devolucion->id
                ]);
            }

            return response()->json([
                'success' => false,
                'error'   => 'No existe devolución asociada a esa compra.'
            ]);
        }

        return response()->json([
            'success' => false,
            'error'   => 'No se encontró ninguna compra o venta con ese folio.'
        ]);
    }

    public function detalle($iddevolucion)
    {
        $devolucion = Devolucion::with([
            'venta.cliente',
            'venta.detalles.articulo',
            'venta.sucursal',
            'compra.proveedor',
            'compra.detalles.articulo',
            'compra.sucursal'
        ])->findOrFail($iddevolucion);

        // Pedido multicanal: la referencia vive en order_ecommerce
        if ($devolucion->tipo === 'pedido') {
            $order = \App\Models\ecommerce\order_ecommerce::with(['cliente', 'detalles.producto'])
                ->find($devolucion->referencia_id);

            if (!$order) {
                return response()->json(['success' => false, 'error' => 'No se encontró el pedido asociado.']);
            }

            $movimientos = Movimiento::with(['cajaApertura.cuenta', 'cuenta'])
                ->where('comprobante', 'Pedido #' . $order->order_id)
                ->get()
                ->map(fn($m) => [
                    'cuenta' => $m->cajaApertura?->cuenta?->nombre ?? $m->cuenta?->nombre ?? '—',
                    'fecha'  => $m->fecha?->format('d/m/Y H:i'),
                    'tipo'   => ucfirst($m->tipo),
                    'total'  => $m->total,
                ]);

            return response()->json([
                'success' => true,
                'devolucion' => [
                    'id'          => $devolucion->id,
                    'folio'       => 'Pedido #' . $order->order_id,
                    'tipo'        => 'Pedido',
                    'sucursal'    => '—',
                    'persona'     => optional($order->cliente)->nombre ?? '—',
                    'movimientos' => $movimientos,
                    'stock'       => $order->detalles->map(fn($d) => [
                        'articulo' => optional($d->producto)->nombre ?? '—',
                        'cantidad' => $d->quantity,
                    ]),
                ],
            ]);
        }

        // Determinar referencia según tipo
        $referencia = $devolucion->tipo === 'venta' ? $devolucion->venta : $devolucion->compra;

        if (!$referencia) {
            return response()->json([
                'success' => false,
                'error'   => 'No se encontró la referencia asociada a la devolución.'
            ]);
        }

        // Cliente o proveedor
        $persona = $devolucion->tipo === 'venta'
            ? optional($referencia->cliente)->nombre
            : optional($referencia->proveedor)->nombre;

        // Sucursal
        $sucursal = $referencia->sucursal->nombre ?? '—';

        // Stock reinvertido: detalles de la referencia
        $stock = $referencia->detalles->map(fn($d) => [
            'articulo' => $d->articulo->nombre,
            'cantidad' => $d->cantidad,
        ]);

        // Movimientos reinvertidos: buscar por comprobante (folio)
        $movimientos = Movimiento::with(['cajaApertura.cuenta', 'cuenta'])
            ->where('comprobante', $referencia->num_folio)
            ->get()
            ->map(fn($m) => [
                'cuenta' => $m->cajaApertura?->cuenta?->nombre 
                            ?? $m->cuenta?->nombre 
                            ?? '—',
                'fecha'  => $m->fecha?->format('d/m/Y H:i'),
                'tipo'   => ucfirst($m->tipo),
                'total'  => $m->total,
            ]);

        return response()->json([
            'success' => true,
            'devolucion' => [
                'id'        => $devolucion->id,
                'folio'     => $referencia->num_folio,
                'tipo'      => ucfirst($devolucion->tipo),
                'sucursal'  => $sucursal,
                'persona'   => $persona,
                'movimientos' => $movimientos,
                'stock'       => $stock,
            ]
        ]);
    }

    public function anularCompra(Request $request, $idcompra)
    {
        DB::beginTransaction();
        try {
            $compra = Compra::with(['movimientos.cuenta', 'movimientos.cajaApertura', 'detalles'])->findOrFail($idcompra);

            if ($compra->estado === 'anulada') {
                return response()->json(['success' => false, 'error' => 'La compra ya está anulada.']);
            }

            Devolucion::create([
                'tipo'          => 'compra',
                'referencia_id' => $compra->idcompra,
                'motivo'        => 'Anulación de compra',
                'fecha'         => now(),
                'monto'         => $compra->total_con_iva,
            ]);

            $pendientes = ['efectivo'=>0,'bancos'=>0,'tarjetas'=>0,'total'=>0];

            foreach ($compra->movimientos as $mov) {
                $cuenta = $mov->cuenta;

                if ($cuenta->tipo === 'caja') {
                    $apertura = $mov->cajaApertura;
                    if ($apertura && $apertura->estaAbierta()) {
                        Movimiento::create([
                            'cuenta_id'        => $cuenta->id,
                            'caja_apertura_id' => $apertura->id,
                            'fecha'            => now(),
                            'tipo'             => 'ingreso',
                            'cliente_proveedor'=> optional($compra->proveedor)->nombre ?? 'Proveedor',
                            'comprobante'      => $compra->num_folio,
                            'observaciones'    => 'Devolución inversa de compra',
                            'efectivo'         => $mov->efectivo,
                            'bancos'           => $mov->bancos,
                            'tarjetas'         => $mov->tarjetas,
                            'total'            => $mov->total,
                        ]);
                    } else {
                        $pendientes['efectivo'] += $mov->efectivo;
                        $pendientes['bancos']   += $mov->bancos;
                        $pendientes['tarjetas'] += $mov->tarjetas;
                        $pendientes['total']    += $mov->total;
                    }
                } elseif ($cuenta->tipo === 'banco') {
                    Movimiento::create([
                        'cuenta_id'        => $cuenta->id,
                        'caja_apertura_id' => null,
                        'fecha'            => now(),
                        'tipo'             => 'ingreso',
                        'cliente_proveedor'=> optional($compra->proveedor)->nombre ?? 'Proveedor',
                        'comprobante'      => $compra->num_folio,
                        'observaciones'    => 'Devolución inversa de compra (Banco)',
                        'efectivo'         => 0,
                        'bancos'           => $mov->bancos,
                        'tarjetas'         => $mov->tarjetas,
                        'total'            => $mov->total,
                    ]);
                }
            }

            if ($pendientes['total'] > 0) {
                $cuentaId = $request->input('cuenta_id');
                if (!$cuentaId) {
                    // 🔹 Traer aperturas abiertas de cajas
                    $cajasAbiertas = CajaApertura::with('cuenta')
                        ->where('abierta', true)
                        ->whereNull('fecha_cierre')
                        ->whereHas('cuenta', function ($q) use ($compra) {
                            $q->where('sucursal_id', $compra->sucursal_id)
                            ->where('tipo', 'caja');
                        })
                        ->get()
                        ->map(fn($apertura) => [
                            'id'            => 'caja-'.$apertura->id,
                            'nombre'        => $apertura->cuenta->nombre,
                            'tipo'          => 'caja',
                            'fecha_apertura'=> $apertura->fecha_apertura->format('d/m/Y H:i'),
                        ])
                        ->values(); // 🔹 asegura colección limpia

                    // 🔹 Traer cuentas de bancos
                    $bancos = Cuenta::with('moneda')
                        ->where('sucursal_id', $compra->sucursal_id)
                        ->where('tipo', 'banco')
                        ->get()
                        ->map(fn($c) => [
                            'id'     => 'banco-'.$c->id,
                            'nombre' => $c->nombre,
                            'tipo'   => 'banco',
                            'moneda' => $c->moneda->codigo,
                        ])
                        ->values();

                    // 🔹 Unir como arrays
                    $cuentasDisponibles = $cajasAbiertas->concat($bancos)->all();

                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error'   => 'Seleccione una cuenta para consolidar los movimientos pendientes.',
                        'cuentas' => $cuentasDisponibles
                    ]);
                }

                // 🔹 Procesar selección
                if (str_starts_with($cuentaId, 'caja-')) {
                    $aperturaId = (int) str_replace('caja-', '', $cuentaId);
                    $aperturaSeleccionada = CajaApertura::findOrFail($aperturaId);

                    if (!$aperturaSeleccionada->estaAbierta()) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'error'   => 'La caja seleccionada no está abierta.'
                        ]);
                    }

                    Movimiento::create([
                        'cuenta_id'        => $aperturaSeleccionada->cuenta_id,
                        'caja_apertura_id' => $aperturaSeleccionada->id,
                        'fecha'            => now(),
                        'tipo'             => 'ingreso',
                        'cliente_proveedor'=> optional($compra->proveedor)->nombre ?? 'Proveedor',
                        'comprobante'      => $compra->num_folio,
                        'observaciones'    => 'Devolución pendientes consolidada de compra',
                        'efectivo'         => $pendientes['total'],
                        'bancos'           => 0,
                        'tarjetas'         => 0,
                        'total'            => $pendientes['total'],
                    ]);
                } elseif (str_starts_with($cuentaId, 'banco-')) {
                    $cuentaBancoId = (int) str_replace('banco-', '', $cuentaId);
                    $cuenta = Cuenta::findOrFail($cuentaBancoId);

                    Movimiento::create([
                        'cuenta_id'        => $cuenta->id,
                        'caja_apertura_id' => null,
                        'fecha'            => now(),
                        'tipo'             => 'ingreso',
                        'cliente_proveedor'=> optional($compra->proveedor)->nombre ?? 'Proveedor',
                        'comprobante'      => $compra->num_folio,
                        'observaciones'    => 'Devolución pendientes consolidada de compra (Banco)',
                        'efectivo'         => 0,
                        'bancos'           => $pendientes['total'],
                        'tarjetas'         => 0,
                        'total'            => $pendientes['total'],
                    ]);
                }
            }

            foreach ($compra->detalles as $detalle) {
                app(StockController::class)->disminuirStockEnSucursal(
                    $compra->sucursal_id,
                    $detalle->articulo_id,
                    $detalle->cantidad,
                    $detalle->combinacion_id
                );
            }

            $compra->estado = 'anulada';
            $compra->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function anularVenta(Request $request, $idventa)
    {
        $venta = Venta::with(['movimientos.cuenta', 'movimientos.cajaApertura', 'detalles'])->findOrFail($idventa);

        if ($venta->estado === 'anulada') {
            return response()->json(['success' => false, 'error' => 'La venta ya está anulada.']);
        }

        if ($request->input('resolucion') === 'cambio') {
            return $this->cambiarProductoVenta($request, $venta);
        }

        DB::beginTransaction();
        try {
            Devolucion::create([
                'tipo'          => 'venta',
                'resolucion'    => 'reintegro',
                'referencia_id' => $venta->idventa,
                'motivo'        => $request->input('motivo') ?: 'Anulación de venta',
                'fecha'         => now(),
                'monto'         => $venta->total_con_iva,
                'diferencia'    => -$venta->total_con_iva,
                'user_id'       => auth()->id(),
            ]);

            $pendientes = ['efectivo'=>0,'bancos'=>0,'tarjetas'=>0,'total'=>0];

            foreach ($venta->movimientos as $mov) {
                $cuenta = $mov->cuenta;

                if ($cuenta->tipo === 'caja') {
                    $apertura = $mov->cajaApertura;
                    if ($apertura && $apertura->estaAbierta()) {
                        Movimiento::create([
                            'cuenta_id'        => $cuenta->id,
                            'caja_apertura_id' => $apertura->id,
                            'fecha'            => now(),
                            'tipo'             => 'egreso',
                            'cliente_proveedor'=> optional($venta->cliente)->nombre ?? 'Cliente',
                            'comprobante'      => $venta->num_folio,
                            'observaciones'    => 'Devolución inversa de venta',
                            'efectivo'         => $mov->efectivo,
                            'bancos'           => $mov->bancos,
                            'tarjetas'         => $mov->tarjetas,
                            'total'            => $mov->total,
                        ]);
                    } else {
                        $pendientes['efectivo'] += $mov->efectivo;
                        $pendientes['bancos']   += $mov->bancos;
                        $pendientes['tarjetas'] += $mov->tarjetas;
                        $pendientes['total']    += $mov->total;
                    }
                } elseif ($cuenta->tipo === 'banco') {
                    Movimiento::create([
                        'cuenta_id'        => $cuenta->id,
                        'caja_apertura_id' => null,
                        'fecha'            => now(),
                        'tipo'             => 'egreso',
                        'cliente_proveedor'=> optional($venta->cliente)->nombre ?? 'Cliente',
                        'comprobante'      => $venta->num_folio,
                        'observaciones'    => 'Devolución inversa de venta (Banco)',
                        'efectivo'         => 0,
                        'bancos'           => $mov->bancos,
                        'tarjetas'         => $mov->tarjetas,
                        'total'            => $mov->total,
                    ]);
                }
            }

            if ($pendientes['total'] > 0) {
                $cuentaId = $request->input('cuenta_id');
                if (!$cuentaId) {
                    // 🔹 Traer aperturas abiertas de cajas
                    $cajasAbiertas = CajaApertura::with('cuenta')
                        ->where('abierta', true)
                        ->whereNull('fecha_cierre')
                        ->whereHas('cuenta', function ($q) use ($venta) {
                            $q->where('sucursal_id', $venta->sucursal_id)
                            ->where('tipo', 'caja');
                        })
                        ->get()
                        ->map(fn($apertura) => [
                            'id'            => 'caja-'.$apertura->id,
                            'nombre'        => $apertura->cuenta->nombre,
                            'tipo'          => 'caja',
                            'fecha_apertura'=> $apertura->fecha_apertura->format('d/m/Y H:i'),
                        ])
                        ->values();

                    // 🔹 Traer cuentas de bancos
                    $bancos = Cuenta::with('moneda')
                        ->where('sucursal_id', $venta->sucursal_id)
                        ->where('tipo', 'banco')
                        ->get()
                        ->map(fn($c) => [
                            'id'     => 'banco-'.$c->id,
                            'nombre' => $c->nombre,
                            'tipo'   => 'banco',
                            'moneda' => $c->moneda->codigo,
                        ])
                        ->values();

                    $cuentasDisponibles = $cajasAbiertas->concat($bancos)->all();

                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error'   => 'Seleccione una cuenta para consolidar los movimientos pendientes.',
                        'cuentas' => $cuentasDisponibles
                    ]);
                }

                // 🔹 Procesar selección
                if (str_starts_with($cuentaId, 'caja-')) {
                    $aperturaId = (int) str_replace('caja-', '', $cuentaId);
                    $aperturaSeleccionada = CajaApertura::findOrFail($aperturaId);

                    if (!$aperturaSeleccionada->estaAbierta()) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'error'   => 'La caja seleccionada no está abierta.'
                        ]);
                    }

                    Movimiento::create([
                        'cuenta_id'        => $aperturaSeleccionada->cuenta_id,
                        'caja_apertura_id' => $aperturaSeleccionada->id,
                        'fecha'            => now(),
                        'tipo'             => 'egreso',
                        'cliente_proveedor'=> optional($venta->cliente)->nombre ?? 'Cliente',
                        'comprobante'      => $venta->num_folio,
                        'observaciones'    => 'Devolución pendientes consolidada de venta',
                        'efectivo'         => $pendientes['total'],
                        'bancos'           => 0,
                        'tarjetas'         => 0,
                        'total'            => $pendientes['total'],
                    ]);
                } elseif (str_starts_with($cuentaId, 'banco-')) {
                    $cuentaBancoId = (int) str_replace('banco-', '', $cuentaId);
                    $cuenta = Cuenta::findOrFail($cuentaBancoId);

                    Movimiento::create([
                        'cuenta_id'        => $cuenta->id,
                        'caja_apertura_id' => null,
                        'fecha'            => now(),
                        'tipo'             => 'egreso',
                        'cliente_proveedor'=> optional($venta->cliente)->nombre ?? 'Cliente',
                        'comprobante'      => $venta->num_folio,
                        'observaciones'    => 'Devolución pendientes consolidada de venta (Banco)',
                        'efectivo'         => 0,
                        'bancos'           => $pendientes['total'],
                        'tarjetas'         => 0,
                        'total'            => $pendientes['total'],
                    ]);
                }
            }

            foreach ($venta->detalles as $detalle) {
                app(StockController::class)->incrementarStockEnSucursal(
                    $venta->sucursal_id,
                    $detalle->articulo_id,
                    $detalle->cantidad,
                    $detalle->combinacion_id
                );
            }

            $venta->estado = 'anulada';
            $venta->save();

            DB::commit();

            \App\Models\Notificacion::avisar('devolucion',
                'Devolución: venta ' . ($venta->num_folio ?: '#' . $venta->idventa) . ' anulada ($' . number_format($venta->total_con_iva, 0, ',', '.') . ')',
                'El stock volvió y los cobros se revirtieron según la cuenta elegida.',
                url('ventas?ver=' . $venta->idventa), 'alerta');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Devolucion resuelta como cambio de producto: repone stock de las lineas
     * devueltas, descuenta el del producto nuevo, y ajusta el total de la
     * MISMA venta (no la anula). La diferencia de precio se cobra sola via
     * el flujo normal de registrarPago (si es a favor del negocio) o se
     * devuelve como egreso (si es a favor del cliente) — el llamador nunca
     * elige entre 3 botones fijos, el sistema calcula que corresponde.
     */
    protected function cambiarProductoVenta(Request $request, Venta $venta)
    {
        $request->validate([
            'articulo_nuevo_id'      => 'required|exists:productos,idarticulo',
            'combinacion_nueva_id'   => 'nullable|exists:producto_combinaciones,idcombinacion',
            'tipo_producto_id_nuevo' => 'required|integer|min:1',
            'cantidad_nueva'         => 'required|integer|min:1',
            'precio_unitario_nuevo'  => 'required|numeric|min:0',
            'iva_nuevo'              => 'required|numeric|min:0',
            'descuento_nuevo'        => 'nullable|numeric|min:0|max:100',
            'detalles_devueltos'     => 'nullable|array',
            'detalles_devueltos.*'   => 'integer|exists:detalle_ventas,id_detalle',
            'motivo'                 => 'nullable|string|max:255',
            'cuenta_id'              => 'nullable|string',
        ]);

        $devueltos = $request->filled('detalles_devueltos')
            ? $venta->detalles->whereIn('id_detalle', $request->detalles_devueltos)
            : $venta->detalles;

        if ($devueltos->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Elegí qué producto de la venta se devuelve.']);
        }

        $restantes = $venta->detalles->diff($devueltos);
        $totalNetoRestante = (float) $restantes->sum('subtotal_neto');
        $totalConIvaRestante = (float) $restantes->sum('subtotal_con_iva');

        $descuento = $request->descuento_nuevo ?? 0;
        $precioConDescuento = $request->precio_unitario_nuevo - ($request->precio_unitario_nuevo * $descuento / 100);
        $subtotalNetoNuevo = $request->cantidad_nueva * $precioConDescuento;
        $subtotalConIvaNuevo = $subtotalNetoNuevo + ($subtotalNetoNuevo * ($request->iva_nuevo / 100));

        $nuevoTotalNeto = $totalNetoRestante + $subtotalNetoNuevo;
        $nuevoTotalConIva = $totalConIvaRestante + $subtotalConIvaNuevo;
        $diferencia = round($nuevoTotalConIva - $venta->total_con_iva, 2);

        if ($diferencia < 0) {
            $cuentaId = $request->input('cuenta_id');
            if (!$cuentaId) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Elegí a qué cuenta le sale la diferencia a favor del cliente.',
                    'cuentas' => $this->cuentasParaSucursal($venta->sucursal_id),
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $articuloNuevo = \App\Models\Articulo::findOrFail($request->articulo_nuevo_id);
            $combinacionNueva = $request->combinacion_nueva_id
                ? \App\Models\ProductoCombinacion::find($request->combinacion_nueva_id)
                : null;

            $descripcionAnterior = $devueltos->map(fn ($d) => $d->cantidad . ' x ' . $d->articulo->nombre)->implode(', ');
            $descripcionNueva = $request->cantidad_nueva . ' x ' . $articuloNuevo->nombre . ($combinacionNueva ? ' - ' . $combinacionNueva->combinacion : '');

            // Repone stock de lo devuelto, descuenta el del producto nuevo (sin stock => excepcion => rollback)
            foreach ($devueltos as $detalle) {
                app(StockController::class)->incrementarStockEnSucursal(
                    $venta->sucursal_id, $detalle->articulo_id, $detalle->cantidad, $detalle->combinacion_id
                );
            }
            app(StockController::class)->disminuirStockEnSucursal(
                $venta->sucursal_id, $request->articulo_nuevo_id, $request->cantidad_nueva, $request->combinacion_nueva_id
            );

            foreach ($devueltos as $detalle) {
                $detalle->delete();
            }

            $venta->detalles()->create([
                'articulo_id'      => $request->articulo_nuevo_id,
                'combinacion_id'   => $request->combinacion_nueva_id,
                'tipo_producto_id' => $request->tipo_producto_id_nuevo,
                'cantidad'         => $request->cantidad_nueva,
                'precio_unitario'  => $request->precio_unitario_nuevo,
                'descuento'        => $descuento,
                'iva'              => $request->iva_nuevo,
                'subtotal_neto'    => $subtotalNetoNuevo,
                'subtotal_con_iva' => $subtotalConIvaNuevo,
            ]);

            $venta->total_neto = $nuevoTotalNeto;
            $venta->total_con_iva = $nuevoTotalConIva;

            if ($diferencia > 0.009) {
                $venta->estado = 'a cobrar'; // el cajero la cobra con el flujo normal de registrarPago
            } elseif ($diferencia < -0.009) {
                $cuentaId = $request->input('cuenta_id');
                $monto = abs($diferencia);

                if (str_starts_with($cuentaId, 'caja-')) {
                    $aperturaId = (int) str_replace('caja-', '', $cuentaId);
                    $apertura = CajaApertura::findOrFail($aperturaId);
                    if (!$apertura->estaAbierta()) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'error' => 'La caja seleccionada no está abierta.']);
                    }
                    Movimiento::create([
                        'cuenta_id' => $apertura->cuenta_id, 'caja_apertura_id' => $apertura->id,
                        'fecha' => now(), 'tipo' => 'egreso',
                        'cliente_proveedor' => optional($venta->cliente)->nombre ?? 'Cliente',
                        'comprobante' => $venta->num_folio,
                        'observaciones' => 'Diferencia a favor del cliente por cambio de producto',
                        'efectivo' => $monto, 'bancos' => 0, 'tarjetas' => 0, 'total' => $monto,
                    ]);
                } elseif (str_starts_with($cuentaId, 'banco-')) {
                    $cuenta = Cuenta::findOrFail((int) str_replace('banco-', '', $cuentaId));
                    Movimiento::create([
                        'cuenta_id' => $cuenta->id, 'caja_apertura_id' => null,
                        'fecha' => now(), 'tipo' => 'egreso',
                        'cliente_proveedor' => optional($venta->cliente)->nombre ?? 'Cliente',
                        'comprobante' => $venta->num_folio,
                        'observaciones' => 'Diferencia a favor del cliente por cambio de producto (Banco)',
                        'efectivo' => 0, 'bancos' => $monto, 'tarjetas' => 0, 'total' => $monto,
                    ]);
                }
                $venta->estado = 'cobrada'; // el total nuevo ya quedo saldado con lo ya cobrado
            }
            // diferencia == 0: la venta sigue como estaba, no se mueve plata

            $venta->save();

            Devolucion::create([
                'tipo' => 'venta', 'resolucion' => 'cambio',
                'referencia_id' => $venta->idventa,
                'motivo' => $request->input('motivo') ?: 'Cambio de producto',
                'fecha' => now(), 'monto' => $subtotalConIvaNuevo,
                'producto_anterior' => $descripcionAnterior,
                'producto_nuevo' => $descripcionNueva,
                'diferencia' => $diferencia,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            \App\Models\Notificacion::avisar('devolucion',
                'Cambio de producto: venta ' . ($venta->num_folio ?: '#' . $venta->idventa),
                $descripcionAnterior . ' → ' . $descripcionNueva,
                url('ventas?ver=' . $venta->idventa), 'alerta');

            return response()->json([
                'success' => true,
                'diferencia' => $diferencia,
                'pendiente_cobro' => $diferencia > 0 ? $diferencia : 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /** Cajas abiertas + bancos de una sucursal, para elegir a donde sale/entra una diferencia. */
    protected function cuentasParaSucursal(int $sucursalId): array
    {
        $cajas = CajaApertura::with('cuenta')
            ->where('abierta', true)->whereNull('fecha_cierre')
            ->whereHas('cuenta', fn ($q) => $q->where('sucursal_id', $sucursalId)->where('tipo', 'caja'))
            ->get()
            ->map(fn ($a) => ['id' => 'caja-' . $a->id, 'nombre' => $a->cuenta->nombre, 'tipo' => 'caja']);

        $bancos = Cuenta::where('sucursal_id', $sucursalId)->where('tipo', 'banco')->get()
            ->map(fn ($c) => ['id' => 'banco-' . $c->id, 'nombre' => $c->nombre, 'tipo' => 'banco']);

        return $cajas->concat($bancos)->values()->all();
    }

}