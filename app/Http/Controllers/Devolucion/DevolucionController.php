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
        DB::beginTransaction();
        try {
            $venta = Venta::with(['movimientos.cuenta', 'movimientos.cajaApertura', 'detalles'])->findOrFail($idventa);

            if ($venta->estado === 'anulada') {
                return response()->json(['success' => false, 'error' => 'La venta ya está anulada.']);
            }

            Devolucion::create([
                'tipo'          => 'venta',
                'referencia_id' => $venta->idventa,
                'motivo'        => 'Anulación de venta',
                'fecha'         => now(),
                'monto'         => $venta->total_con_iva,
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
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

}