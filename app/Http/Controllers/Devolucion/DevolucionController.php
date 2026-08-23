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
                        'alias_tercero'    => $mov->alias_tercero,
                        'cuit_tercero'     => $mov->cuit_tercero,
                        'cliente_proveedor'=> optional($order->cliente)->nombre ?? 'Cliente ecommerce',
                        'comprobante'      => $comprobante,
                        'observaciones'    => 'Devolución de pedido' . ($cuenta->tipo === 'tercero' ? ' (Terceros' . ($mov->alias_tercero ? ' — alias ' . $mov->alias_tercero : '') . ')' : ' (Banco)'),
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
                } elseif (in_array($cuenta->tipo, ['banco', 'tercero'])) {
                    // Si el cobro entró por una cuenta de terceros, la reversa sale
                    // del mismo alias para que el control por alias quede en cero
                    Movimiento::create([
                        'cuenta_id'        => $cuenta->id,
                        'caja_apertura_id' => null,
                        'fecha'            => now(),
                        'tipo'             => 'egreso',
                        'alias_tercero'    => $mov->alias_tercero,
                        'cuit_tercero'     => $mov->cuit_tercero,
                        'cliente_proveedor'=> optional($venta->cliente)->nombre ?? 'Cliente',
                        'comprobante'      => $venta->num_folio,
                        'observaciones'    => 'Devolución inversa de venta' . ($cuenta->tipo === 'tercero' ? ' (Terceros' . ($mov->alias_tercero ? ' — alias ' . $mov->alias_tercero : '') . ')' : ' (Banco)'),
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
     * Devolucion resuelta como cambio: cubre todos los escenarios en una sola
     * operacion sobre la MISMA venta (no la anula):
     *  - devolver producto(s) y reintegrar parte de la plata (sin producto nuevo)
     *  - cambiar producto(s) por otro(s), cobrando o devolviendo la diferencia
     *  - agregar producto(s) nuevos sin devolver nada (el cliente quiere mas)
     * La diferencia se calcula automaticamente; si es a favor del cliente sale
     * como egreso de la cuenta elegida, si es a favor del negocio se cobra en
     * el momento en la cuenta elegida (o queda "a cobrar"). Las cuentas pueden
     * ser cajas, bancos o cuentas de terceros (con alias/CUIT dinamico).
     */
    protected function cambiarProductoVenta(Request $request, Venta $venta)
    {
        $request->validate([
            'detalles_devueltos'   => 'nullable|array',
            'detalles_devueltos.*' => 'integer|exists:detalle_ventas,id_detalle',
            'nuevos'                    => 'nullable|array',
            'nuevos.*.articulo_id'      => 'required|exists:productos,idarticulo',
            'nuevos.*.combinacion_id'   => 'nullable|exists:producto_combinaciones,idcombinacion',
            'nuevos.*.tipo_producto_id' => 'required|integer|min:1',
            'nuevos.*.cantidad'         => 'required|integer|min:1',
            'nuevos.*.precio_unitario'  => 'required|numeric|min:0',
            'nuevos.*.iva'              => 'required|numeric|min:0',
            'nuevos.*.descuento'        => 'nullable|numeric|min:0|max:100',
            'motivo'          => 'nullable|string|max:255',
            'cuenta_id'       => 'nullable|string', // de donde sale la plata si la diferencia es a favor del cliente
            'cuenta_cobro_id' => 'nullable|string', // donde entra la plata si el cliente paga diferencia ahora
            'alias_tercero'   => 'nullable|string|max:60',
            'cuit_tercero'    => 'nullable|string|max:20',
        ]);

        $devueltos = $request->filled('detalles_devueltos')
            ? $venta->detalles->whereIn('id_detalle', $request->detalles_devueltos)
            : collect();
        $nuevos = collect($request->input('nuevos', []));

        if ($devueltos->isEmpty() && $nuevos->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Elegí al menos un producto a devolver o un producto nuevo.']);
        }

        $restantes = $venta->detalles->diff($devueltos);
        $totalNetoRestante = (float) $restantes->sum('subtotal_neto');
        $totalConIvaRestante = (float) $restantes->sum('subtotal_con_iva');

        // Subtotales de cada producto nuevo
        $nuevosCalculados = $nuevos->map(function ($n) {
            $descuento = (float) ($n['descuento'] ?? 0);
            $precioConDescuento = $n['precio_unitario'] - ($n['precio_unitario'] * $descuento / 100);
            $subtotalNeto = $n['cantidad'] * $precioConDescuento;
            $subtotalConIva = $subtotalNeto + ($subtotalNeto * ($n['iva'] / 100));
            return array_merge($n, [
                'descuento'        => $descuento,
                'subtotal_neto'    => $subtotalNeto,
                'subtotal_con_iva' => $subtotalConIva,
            ]);
        });

        $nuevoTotalNeto = $totalNetoRestante + $nuevosCalculados->sum('subtotal_neto');
        $nuevoTotalConIva = $totalConIvaRestante + $nuevosCalculados->sum('subtotal_con_iva');
        $diferencia = round($nuevoTotalConIva - $venta->total_con_iva, 2);

        // Diferencia a favor del cliente: hace falta saber de que cuenta sale
        if ($diferencia < -0.009 && !$request->filled('cuenta_id')) {
            return response()->json([
                'success' => false,
                'error'   => 'Elegí de qué cuenta sale la plata a devolver al cliente.',
                'cuentas' => $this->cuentasParaSucursal($venta->sucursal_id),
            ]);
        }

        DB::beginTransaction();
        try {
            $descripcionAnterior = $devueltos->map(fn ($d) => $d->cantidad . ' x ' . $d->articulo->nombre . ($d->combinacion_id && $d->combinacion ? ' - ' . $d->combinacion->combinacion : ''))->implode(', ') ?: '—';

            // Stock: repone lo devuelto, descuenta cada producto nuevo (sin stock => excepcion => rollback)
            foreach ($devueltos as $detalle) {
                app(StockController::class)->incrementarStockEnSucursal(
                    $venta->sucursal_id, $detalle->articulo_id, $detalle->cantidad, $detalle->combinacion_id
                );
            }

            $descripcionesNuevas = [];
            foreach ($nuevosCalculados as $n) {
                app(StockController::class)->disminuirStockEnSucursal(
                    $venta->sucursal_id, $n['articulo_id'], $n['cantidad'], $n['combinacion_id'] ?? null
                );

                $articulo = \App\Models\Articulo::findOrFail($n['articulo_id']);
                $combinacion = !empty($n['combinacion_id']) ? \App\Models\ProductoCombinacion::find($n['combinacion_id']) : null;
                $descripcionesNuevas[] = $n['cantidad'] . ' x ' . $articulo->nombre . ($combinacion ? ' - ' . $combinacion->combinacion : '');

                $venta->detalles()->create([
                    'articulo_id'      => $n['articulo_id'],
                    'combinacion_id'   => $n['combinacion_id'] ?? null,
                    'tipo_producto_id' => $n['tipo_producto_id'],
                    'cantidad'         => $n['cantidad'],
                    'precio_unitario'  => $n['precio_unitario'],
                    'descuento'        => $n['descuento'],
                    'iva'              => $n['iva'],
                    'subtotal_neto'    => $n['subtotal_neto'],
                    'subtotal_con_iva' => $n['subtotal_con_iva'],
                ]);
            }
            $descripcionNueva = $descripcionesNuevas ? implode(', ', $descripcionesNuevas) : '—';

            foreach ($devueltos as $detalle) {
                $detalle->delete();
            }

            $venta->total_neto = $nuevoTotalNeto;
            $venta->total_con_iva = $nuevoTotalConIva;

            $nombreCliente = optional($venta->cliente)->nombre ?? 'Cliente';

            if ($diferencia < -0.009) {
                // Hay que devolverle plata al cliente
                $mov = $this->crearMovimientoDiferencia(
                    $request->input('cuenta_id'), 'egreso', abs($diferencia), $nombreCliente,
                    $venta->num_folio, 'Devolución al cliente por cambio/devolución parcial',
                    $request->input('alias_tercero'), $request->input('cuit_tercero')
                );
                if (!$mov['ok']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'error' => $mov['error']]);
                }
                $venta->estado = 'cobrada'; // el total nuevo quedo saldado con lo ya cobrado
            } elseif ($diferencia > 0.009) {
                if ($request->filled('cuenta_cobro_id')) {
                    // El cliente paga la diferencia en el momento
                    $mov = $this->crearMovimientoDiferencia(
                        $request->input('cuenta_cobro_id'), 'ingreso', $diferencia, $nombreCliente,
                        $venta->num_folio, 'Cobro de diferencia por cambio de producto',
                        $request->input('alias_tercero'), $request->input('cuit_tercero')
                    );
                    if (!$mov['ok']) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'error' => $mov['error']]);
                    }
                    $venta->estado = 'cobrada';
                } else {
                    $venta->estado = 'a cobrar'; // queda pendiente, se cobra con el flujo normal
                }
            }
            // diferencia == 0: cambio mano a mano, no se mueve plata

            $venta->save();

            Devolucion::create([
                'tipo' => 'venta', 'resolucion' => 'cambio',
                'referencia_id' => $venta->idventa,
                'motivo' => $request->input('motivo') ?: 'Cambio/devolución parcial',
                'fecha' => now(), 'monto' => (float) $nuevosCalculados->sum('subtotal_con_iva'),
                'producto_anterior' => $descripcionAnterior,
                'producto_nuevo' => $descripcionNueva,
                'diferencia' => $diferencia,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            \App\Models\Notificacion::avisar('devolucion',
                'Cambio/devolución: venta ' . ($venta->num_folio ?: '#' . $venta->idventa),
                $descripcionAnterior . ' → ' . $descripcionNueva
                . ($diferencia > 0.009 ? ' (diferencia cobrada/a cobrar $' . number_format($diferencia, 0, ',', '.') . ')' : '')
                . ($diferencia < -0.009 ? ' (devuelto $' . number_format(abs($diferencia), 0, ',', '.') . ')' : ''),
                url('ventas?ver=' . $venta->idventa), 'alerta');

            return response()->json([
                'success' => true,
                'diferencia' => $diferencia,
                'pendiente_cobro' => ($diferencia > 0.009 && !$request->filled('cuenta_cobro_id')) ? $diferencia : 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Crea el movimiento de la diferencia (ingreso o egreso) en una caja
     * abierta, un banco o una cuenta de terceros (con alias/CUIT dinamico).
     * Devuelve ['ok' => bool, 'error' => string|null].
     */
    protected function crearMovimientoDiferencia(?string $cuentaRef, string $tipo, float $monto, string $persona, ?string $comprobante, string $obs, ?string $alias = null, ?string $cuit = null): array
    {
        if (!$cuentaRef) {
            return ['ok' => false, 'error' => 'Elegí la cuenta para registrar la diferencia.'];
        }

        $base = [
            'fecha' => now(), 'tipo' => $tipo,
            'cliente_proveedor' => $persona,
            'comprobante' => $comprobante,
            'efectivo' => 0, 'bancos' => 0, 'tarjetas' => 0, 'cheques' => 0,
            'total' => $monto,
        ];

        if (str_starts_with($cuentaRef, 'caja-')) {
            $apertura = CajaApertura::find((int) str_replace('caja-', '', $cuentaRef));
            if (!$apertura || !$apertura->estaAbierta()) {
                return ['ok' => false, 'error' => 'La caja seleccionada no está abierta.'];
            }
            Movimiento::create(array_merge($base, [
                'cuenta_id' => $apertura->cuenta_id, 'caja_apertura_id' => $apertura->id,
                'medio' => 'efectivo', 'observaciones' => $obs,
                'efectivo' => $monto,
            ]));
            return ['ok' => true, 'error' => null];
        }

        if (str_starts_with($cuentaRef, 'banco-')) {
            $cuenta = Cuenta::find((int) str_replace('banco-', '', $cuentaRef));
            if (!$cuenta) {
                return ['ok' => false, 'error' => 'No se encontró la cuenta banco seleccionada.'];
            }
            Movimiento::create(array_merge($base, [
                'cuenta_id' => $cuenta->id, 'caja_apertura_id' => null,
                'medio' => 'transferencia', 'observaciones' => $obs . ' (Banco)',
                'bancos' => $monto,
            ]));
            return ['ok' => true, 'error' => null];
        }

        if (str_starts_with($cuentaRef, 'tercero-')) {
            $cuenta = Cuenta::find((int) str_replace('tercero-', '', $cuentaRef));
            if (!$cuenta || $cuenta->tipo !== 'tercero') {
                return ['ok' => false, 'error' => 'No se encontró la cuenta de terceros seleccionada.'];
            }
            $alias = trim((string) $alias);
            if (!$alias) {
                return ['ok' => false, 'error' => 'Indicá el alias del tercero por el que pasa la plata.'];
            }
            Movimiento::create(array_merge($base, [
                'cuenta_id' => $cuenta->id, 'caja_apertura_id' => null,
                'medio' => 'transferencia',
                'alias_tercero' => $alias,
                'cuit_tercero' => trim((string) $cuit) ?: null,
                'observaciones' => $obs . ' — alias ' . $alias,
                'bancos' => $monto,
            ]));
            return ['ok' => true, 'error' => null];
        }

        return ['ok' => false, 'error' => 'Cuenta seleccionada inválida.'];
    }

    /** Cajas abiertas + bancos + cuentas de terceros de una sucursal, para la diferencia. */
    protected function cuentasParaSucursal(int $sucursalId): array
    {
        $cajas = CajaApertura::with('cuenta')
            ->where('abierta', true)->whereNull('fecha_cierre')
            ->whereHas('cuenta', fn ($q) => $q->where('sucursal_id', $sucursalId)->where('tipo', 'caja'))
            ->get()
            ->map(fn ($a) => ['id' => 'caja-' . $a->id, 'nombre' => $a->cuenta->nombre, 'tipo' => 'caja']);

        $bancos = Cuenta::where('sucursal_id', $sucursalId)->where('tipo', 'banco')->where('activa', 1)->get()
            ->map(fn ($c) => ['id' => 'banco-' . $c->id, 'nombre' => $c->nombre, 'tipo' => 'banco']);

        $terceros = Cuenta::where('tipo', 'tercero')->where('activa', 1)->get()
            ->map(fn ($c) => ['id' => 'tercero-' . $c->id, 'nombre' => $c->nombre, 'tipo' => 'tercero']);

        return $cajas->concat($bancos)->concat($terceros)->values()->all();
    }

}