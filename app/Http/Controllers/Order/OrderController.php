<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
use App\Models\ecommerce\order_ecommerce;
use App\Models\ecommerce\status_order_ecommerce;
use App\Models\ecommerce\payment_methods;
use App\Models\ecommerce\payment_ecommerce;
use App\Models\ecommerce\order_stock_asignacion;

use App\Models\CajaApertura;
use App\Models\Movimiento;
use App\Http\Controllers\StockController;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Cantidad de pedidos pendientes + último id (para la alerta del header).
     * Incluye pedidos "estancados": más de 48 hs sin salir de Pendiente.
     */
    public function newCount()
    {
        $pendientes = order_ecommerce::where('status_order_id', 1)->where('active', 1)->count();
        $ultimoId = (int) order_ecommerce::max('order_id');
        $estancados = order_ecommerce::where('status_order_id', 1)
            ->where('active', 1)
            ->where('order_date', '<', now()->subHours(48))
            ->count();

        return response()->json([
            'pendientes' => $pendientes,
            'ultimo_id'  => $ultimoId,
            'estancados' => $estancados,
        ]);
    }

    /**
     * Programar (o cambiar) la fecha de entrega de un pedido.
     */
    public function setEntrega(Request $request, $id)
    {
        $request->validate(['fecha_entrega' => 'required|date']);

        $order = order_ecommerce::findOrFail($id);
        $order->update(['fecha_entrega' => $request->fecha_entrega]);

        // Avisar al cliente la fecha programada
        try {
            $cliente = $order->cliente;
            if ($cliente && !empty($cliente->email)) {
                \Illuminate\Support\Facades\Mail::to($cliente->email)->send(
                    new \App\Mail\PedidoEstadoMail(
                        $order->fresh(), $cliente,
                        'Programamos la entrega de tu pedido',
                        'Tu pedido tiene fecha de entrega programada para el ' . \Carbon\Carbon::parse($request->fecha_entrega)->format('d/m/Y') . '. Si ese día no te queda cómodo, escribinos por WhatsApp y lo reprogramamos.'
                    )
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo enviar el aviso de entrega del pedido #' . $order->order_id . ': ' . $e->getMessage());
        }

        return back()->with('message', 'Fecha de entrega programada: ' . \Carbon\Carbon::parse($request->fecha_entrega)->format('d/m/Y'))
            ->with('typealert', 'success');
    }

    public function index()
    {
        // Catálogo de estados + conteo de pedidos activos en cada uno (para las pestañas del pipeline)
        $estados = status_order_ecommerce::where('active', 1)->orderBy('status_id')->get();
        $conteos = order_ecommerce::where('active', 1)
            ->selectRaw('status_order_id, COUNT(*) as n')
            ->groupBy('status_order_id')
            ->pluck('n', 'status_order_id');

        return view('order.index', compact('estados', 'conteos'));
    }

    /**
     * Formulario de carga manual de pedidos de otros canales (ML, WhatsApp, IG, local).
     */
    public function createManual()
    {
        $productos = DB::table('productos as p')
            ->leftJoin('sucursal_articulo as sa', function ($j) {
                $j->on('sa.articulo_id', '=', 'p.idarticulo')->where('sa.activo', 1);
            })
            ->where('p.estado', 'Activo')
            ->groupBy('p.idarticulo', 'p.nombre', 'p.pventa_con_iva')
            ->selectRaw('p.idarticulo, p.nombre, p.pventa_con_iva, COALESCE(SUM(sa.stock),0) as stock')
            ->orderBy('p.nombre')
            ->get();

        return view('order.manual', compact('productos'));
    }

    /**
     * Registra el pedido manual: cliente (busca o crea por email/teléfono) + orden + detalle.
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'origen'          => 'required|in:meli,whatsapp,instagram,facebook,local',
            'nombre'          => 'required|string|max:100',
            'telefono'        => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:150',
            'direccion'       => 'nullable|string|max:200',
            'items'           => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer',
            'items.*.cantidad'    => 'required|integer|min:1',
            'items.*.precio'      => 'required|numeric|min:0',
        ], [
            'items.required' => 'Agregá al menos un producto al pedido.',
            'nombre.required' => 'Ingresá el nombre del cliente.',
        ]);

        DB::beginTransaction();
        try {
            // Cliente: reusar por email o teléfono; si no existe, crearlo
            $cliente = null;
            if ($request->filled('email')) {
                $cliente = \App\Models\Cliente::where('email', trim($request->email))->first();
            }
            if (!$cliente && $request->filled('telefono')) {
                $cliente = \App\Models\Cliente::where('telefono', trim($request->telefono))->first();
            }
            if (!$cliente) {
                $cliente = \App\Models\Cliente::create([
                    'nombre'    => $request->nombre,
                    'email'     => $request->email ?? '',
                    'telefono'  => $request->telefono ?? '',
                    'direccion' => $request->direccion ?? '',
                    'estatus'   => 1,
                ]);
            }

            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['cantidad'] * $item['precio'];
            }

            $order = order_ecommerce::create([
                'status_order_id' => 1,
                'cliente_id'      => $cliente->idcliente,
                'origen'          => $request->origen,
                'subtotal_amount' => $subtotal,
                'total_amount'    => $subtotal,
                'order_date'      => now(),
                'costo_envio'     => 0,
                'descuento_pago'  => 0,
                'additional_info' => 'Pedido cargado manualmente desde el panel (' . $request->origen . ')',
            ]);

            foreach ($request->items as $item) {
                \App\Models\ecommerce\order_detail_ecommerce::create([
                    'order_ecommerce_id' => $order->order_id,
                    'product_id'         => $item['producto_id'],
                    'quantity'           => $item['cantidad'],
                    'price'              => $item['precio'],
                    'total'              => $item['cantidad'] * $item['precio'],
                ]);
            }

            DB::commit();

            \App\Models\Notificacion::avisar('pedido',
                'Pedido manual #' . $order->order_id . ' (' . $request->origen . ') por $' . number_format($subtotal, 0, ',', '.'),
                'Cliente: ' . $request->nombre,
                url('orders/order/' . $order->order_id));

            return redirect('/orders/order/' . $order->order_id)
                ->with('message', 'Pedido #' . $order->order_id . ' cargado correctamente.')
                ->with('typealert', 'success');
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->withInput()->withErrors(['items' => 'No se pudo registrar el pedido: ' . $th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $orders = order_ecommerce::with(['status','cliente','detalles.producto'])
            ->where('active', true)
            ->when(request()->filled('status_id'), function ($q) {
                $q->where('status_order_id', request('status_id'));
            })
            ->get()
            ->map(function($order){
                // 🔹 Definir ícono según estado
                $icon = '<i class="fas fa-eye text-primary mr-3" title="Ver el pedido"></i>';
                if ($order->status->status_name === 'Entregado') {
                    $icon = '<i class="fas fa-check text-success mr-3" title="Pedido entregado"></i>';
                }

                // Etiqueta visual del canal de venta
                $canales = [
                    'tienda'    => ['Tienda online', '#1B2B5A'],
                    'meli'      => ['MercadoLibre', '#e6b800'],
                    'whatsapp'  => ['WhatsApp', '#1EBE5A'],
                    'instagram' => ['Instagram', '#C13584'],
                    'facebook'  => ['Facebook', '#1877F2'],
                    'local'     => ['Local', '#47536F'],
                ];
                $origenKey = $order->origen ?? 'tienda';
                [$canalNombre, $canalColor] = $canales[$origenKey] ?? [ucfirst($origenKey), '#47536F'];

                // Cliente: nombre + teléfono
                $clienteNombre = trim((optional($order->cliente)->nombre ?? '') . ' ' . (optional($order->cliente)->paterno ?? '')) ?: '—';
                $telefono = optional($order->cliente)->telefono;
                $clienteHtml = '<div style="font-weight:700;color:#1B2B5A;">'.e($clienteNombre).'</div>'
                    . ($telefono ? '<div style="font-size:0.78rem;"><a href="https://wa.me/'.preg_replace('/\D/', '', $telefono).'" target="_blank" rel="noopener" style="color:#0d8a4f;text-decoration:none;"><i class="fab fa-whatsapp"></i> '.e($telefono).'</a></div>' : '');

                // Dirección de entrega: calle del cliente + localidad/provincia/CP del pedido
                $direccion = trim(implode(', ', array_filter([
                    optional($order->cliente)->direccion,
                    $order->direccion_localidad,
                    $order->direccion_provincia,
                ])));
                if ($order->direccion_cp) {
                    $direccion .= ' (CP ' . $order->direccion_cp . ')';
                }
                $direccionHtml = $direccion
                    ? '<span style="font-size:0.82rem;color:#47536F;"><i class="fas fa-map-marker-alt text-muted"></i> '.e($direccion).'</span>'
                    : '<span class="text-muted">Sin dirección</span>';

                // Productos del pedido
                $productosHtml = $order->detalles->map(function ($d) {
                    return '<div style="font-size:0.82rem;color:#1B2B5A;"><strong>'.$d->quantity.'x</strong> '.e(optional($d->producto)->nombre ?? '—').'</div>';
                })->implode('') ?: '<span class="text-muted">—</span>';

                return [
                    'order_id'     => $order->order_id,
                    'canal'        => '<span class="badge" style="background:'.$canalColor.';color:#fff;">'.$canalNombre.'</span>',
                    'cliente'      => $clienteHtml,
                    'direccion'    => $direccionHtml,
                    'productos'    => $productosHtml,
                    'order_date'   => $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') : '—',
                    'total_amount' => '<strong>$'.number_format($order->total_amount, 2, ',', '.').'</strong>',
                    'statusName'   => $order->status->status_name,
                    'action'       => '
                        <div class="text-center">
                            <a href="/orders/order/'.$order->order_id.'">
                                '.$icon.'
                            </a>
                        </div>'
                ];
            });

        return DataTables::of($orders)
            ->editColumn('statusName', function($order){
                $badgeClass = match($order['statusName']) {
                    'Pagado' => 'bg-warning',
                    'Cancelado' => 'bg-danger',
                    'Pendiente' => 'bg-secondary',
                    'Comprobación de stock' => 'bg-info',
                    'Enviado' => 'bg-primary',
                    'Entregado' => 'bg-success',
                    default => 'bg-light'
                };
                return '<h5><span class="badge '.$badgeClass.'">'.$order['statusName'].'</span></h5>';
            })
            ->rawColumns(['action','statusName','canal','cliente','direccion','productos','total_amount'])
            ->make(true);
    }

    /**
     * Pagos del pedido: transacciones registradas (en una o varias cuentas),
     * total/pagado/pendiente y destinos disponibles para registrar más pagos.
     */
    public function pagos($id)
    {
        $order = order_ecommerce::with(['pago', 'cliente'])->findOrFail($id);

        $movs = Movimiento::with('cuenta')
            ->where('comprobante', 'Pedido #' . $order->order_id)
            ->orderBy('fecha')
            ->get();

        $pagado = round(
            $movs->where('tipo', 'ingreso')->sum('total') - $movs->where('tipo', 'egreso')->sum('total'),
            2
        );
        $total = (float) $order->total_amount;

        $cajasAbiertas = CajaApertura::with('cuenta')
            ->where('abierta', true)->whereNull('fecha_cierre')->get()
            ->map(fn ($a) => ['id' => 'caja-' . $a->id, 'nombre' => ($a->cuenta->nombre ?? 'Caja') . ' — caja abierta']);

        $bancos = \App\Models\Cuenta::where('tipo', 'banco')->where('activa', 1)->get()
            ->map(fn ($c) => ['id' => 'banco-' . $c->id, 'nombre' => $c->nombre . ' — banco']);

        $terceros = \App\Models\Cuenta::where('tipo', 'tercero')->where('activa', 1)->get()
            ->map(fn ($c) => ['id' => 'tercero-' . $c->id, 'nombre' => $c->nombre . ' — ' . $c->alias . ' (tercero)']);

        return response()->json([
            'total'     => $total,
            'pagado'    => $pagado,
            'pendiente' => round(max(0, $total - $pagado), 2),
            'mp'        => ($order->pago && $order->pago->mp_payment_id) ? [
                'status'     => $order->pago->status_payment,
                'payment_id' => $order->pago->mp_payment_id,
            ] : null,
            'movimientos' => $movs->map(fn ($m) => [
                'id'            => $m->id,
                'fecha'         => $m->fecha ? \Carbon\Carbon::parse($m->fecha)->format('d/m/Y H:i') : '—',
                'cuenta'        => $m->cuenta->nombre ?? '—',
                'tipo'          => $m->tipo,
                'medio'         => $m->efectivo > 0 ? 'Efectivo' : ($m->tarjetas > 0 ? 'Tarjeta' : 'Banco/Transf.'),
                'observaciones' => $m->observaciones,
                'adjunto_url'   => $m->adjunto_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($m->adjunto_path) : null,
                'adjunto_nombre'=> $m->adjunto_nombre,
                'monto'         => (float) $m->total,
            ])->values(),
            'destinos' => $cajasAbiertas->concat($bancos)->concat($terceros)->values(),
        ]);
    }

    /**
     * Registra un pago (total o parcial) del pedido en la cuenta elegida.
     * Permite cobrar un mismo pedido repartido en varias cuentas/medios.
     */
    public function registrarPago(Request $request, $id)
    {
        $request->validate([
            'destino'       => 'required|string',
            'medio'         => 'required|in:efectivo,transferencia,tarjeta',
            'monto'         => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:255',
            'comprobante'   => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt|max:8192',
        ], [
            'comprobante.mimes' => 'El archivo debe ser imagen, PDF, Word, Excel o TXT.',
            'comprobante.max'   => 'El archivo puede pesar como máximo 8 MB.',
        ]);

        $order = order_ecommerce::with('cliente')->findOrFail($id);

        $pagado = Movimiento::where('comprobante', 'Pedido #' . $order->order_id)
            ->selectRaw("SUM(CASE WHEN tipo='ingreso' THEN total ELSE -total END) as neto")
            ->value('neto') ?? 0;
        $pendiente = round($order->total_amount - $pagado, 2);

        $monto = round((float) $request->monto, 2);
        if ($monto > $pendiente + 0.01) {
            return response()->json([
                'status'  => 0,
                'mensaje' => ['El monto supera lo pendiente ($' . number_format($pendiente, 2, ',', '.') . ').'],
            ]);
        }

        $cuentaId = null;
        $aperturaId = null;
        $tercero = null;

        if (str_starts_with($request->destino, 'caja-')) {
            $aperturaId = (int) str_replace('caja-', '', $request->destino);
            $apertura = CajaApertura::findOrFail($aperturaId);
            if (!$apertura->estaAbierta()) {
                return response()->json(['status' => 0, 'mensaje' => ['La caja seleccionada no está abierta.']]);
            }
            $cuentaId = $apertura->cuenta_id;
        } elseif (str_starts_with($request->destino, 'banco-')) {
            $cuentaId = (int) str_replace('banco-', '', $request->destino);
        } elseif (str_starts_with($request->destino, 'tercero-')) {
            $cuentaId = (int) str_replace('tercero-', '', $request->destino);
            $tercero = \App\Models\Cuenta::find($cuentaId);
        } else {
            return response()->json(['status' => 0, 'mensaje' => ['Destino de pago inválido.']]);
        }

        $adjuntoPath = null;
        $adjuntoNombre = null;
        if ($request->hasFile('comprobante')) {
            $adjuntoPath = $request->file('comprobante')->store('pagos/comprobantes', 'public');
            $adjuntoNombre = $request->file('comprobante')->getClientOriginalName();
        }

        Movimiento::create([
            'cuenta_id'         => $cuentaId,
            'caja_apertura_id'  => $aperturaId,
            'fecha'             => now(),
            'tipo'              => 'ingreso',
            'cliente_proveedor' => optional($order->cliente)->nombre ?? 'Cliente ecommerce',
            'comprobante'       => 'Pedido #' . $order->order_id,
            'observaciones'     => ($request->observaciones ?: ('Pago de pedido ecommerce (' . $request->medio . ')'))
                . ($tercero ? ' — transferido a cuenta de terceros: ' . $tercero->alias . ' (CUIT ' . $tercero->cuit . ')' : ''),
            'adjunto_path'      => $adjuntoPath,
            'adjunto_nombre'    => $adjuntoNombre,
            'efectivo'          => $request->medio === 'efectivo' ? $monto : 0,
            'bancos'            => $request->medio === 'transferencia' ? $monto : 0,
            'tarjetas'          => $request->medio === 'tarjeta' ? $monto : 0,
            'total'             => $monto,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Pago registrado' . ($pendiente - $monto > 0.01
                ? '. Quedan pendientes $' . number_format($pendiente - $monto, 2, ',', '.')
                : '. El pedido quedó totalmente pagado. 🎉'),
        ]);
    }

    public function update_status(Request $request)
    {
        try {
            $order = order_ecommerce::with(['detalles.producto'])->find($request->orderId);

            if (!$order) {
                return response()->json([
                    'status' => 0,
                    'mensaje' => ['No se encontró la orden.']
                ]);
            }

            $errores = [];

            // 🔹 Si el estado es comprobación de stock (2)
            if ((int)$request->statusId === 2) {
                foreach ($order->detalles as $detalle) {
                    $cantidadPedida = $detalle->quantity;
                    $cantidadAsignada = 0;

                    $keys = collect($request->all())->keys()->filter(function($k) use ($detalle) {
                        return str_contains($k, "producto_{$detalle->product_id}_sucursal_");
                    });

                    $asignaciones = [];
                    foreach ($keys as $key) {
                        $json = json_decode($request->input($key), true);
                        if ($json && isset($json['sucursal']) && isset($json['cantidad'])) {
                            $cantidadAsignada += (int)$json['cantidad'];
                            $asignaciones[] = $json;
                        }
                    }

                    if ($cantidadAsignada < $cantidadPedida) {
                        $errores[] = "El producto {$detalle->producto->nombre} no tiene stock suficiente asignado. Pedido: {$cantidadPedida}, Asignado: {$cantidadAsignada}";
                    }
                    if ($cantidadAsignada > $cantidadPedida) {
                        $errores[] = "El producto {$detalle->producto->nombre} tiene stock asignado en exceso. Pedido: {$cantidadPedida}, Asignado: {$cantidadAsignada}";
                    }

                    if (empty($errores)) {
                        foreach ($asignaciones as $asig) {
                            [$sucursalId, $combinacionId] = array_pad(explode('-', $asig['sucursal']), 2, null);

                            order_stock_asignacion::create([
                                'order_id'      => $order->order_id,
                                'product_id'    => $detalle->product_id,
                                'combinacion_id'=> $combinacionId,
                                'sucursal_id'   => (int)$sucursalId,
                                'cantidad'      => (int)$asig['cantidad'],
                            ]);
                        }
                    }
                }

                if (!empty($errores)) {
                    return response()->json([
                        'status' => 0,
                        'mensaje' => $errores
                    ]);
                }
            }

            // 🔹 Si el estado es enviado (4)
            if ((int)$request->statusId === 4) {
                // Registrar log de envío si lo necesitás
            }

            // 🔹 Si el estado es entregado (5)
            if ((int)$request->statusId === 5) {
                // Registrar fecha de entrega si tenés columna
                // $order->fecha_entrega = now();
            }

            // 🔹 Si el estado es anulado (6)
            if ((int)$request->statusId === 6) {
                // Eliminar todas las asignaciones de stock de esta orden
                order_stock_asignacion::where('order_id', $order->order_id)->delete();
            }

            // 🤝 Comisión del revendedor: se aprueba al entregar y se anula si se cae el pedido.
            // Una comisión ya pagada no se toca: eso se corrige a mano desde el panel.
            $nuevoEstadoComision = match ((int) $request->statusId) {
                5 => 'aprobada',
                6 => 'anulada',
                default => null,
            };
            if ($nuevoEstadoComision) {
                \App\Models\RevendedorComision::where('order_id', $order->order_id)
                    ->whereIn('estado', ['pendiente', 'aprobada'])
                    ->update(['estado' => $nuevoEstadoComision]);
            }

            // 🔹 Actualizar estado de la orden
            $order->update(['status_order_id' => $request->statusId]);

            $statusOrder = status_order_ecommerce::find($request->statusId);

            // 📩 Aviso automático al cliente en cada cambio de estado (si falla el mail, el cambio no se rompe)
            try {
                $cliente = $order->cliente;
                if ($cliente && !empty($cliente->email)) {
                    $textos = [
                        2 => ['Estamos preparando tu pedido', 'Ya estamos preparando tu pedido y reservando tus productos. Te avisamos apenas esté listo para salir.'],
                        3 => ['¡Recibimos tu pago!', 'Confirmamos el pago de tu pedido. Muchas gracias — seguimos con la preparación para el envío.'],
                        4 => ['Tu pedido está en camino', 'Tu pedido ya salió hacia tu domicilio. Cualquier detalle de la entrega te lo coordinamos por WhatsApp.'],
                        5 => ['¡Tu pedido fue entregado!', 'Registramos la entrega de tu pedido. Que lo disfrutes — y que empiecen las buenas noches de descanso. Ante cualquier consulta o reclamo, estamos a un mensaje de distancia.'],
                        6 => ['Tu pedido fue anulado', 'Tu pedido fue anulado. Si no lo solicitaste o tenés dudas, escribinos y lo resolvemos enseguida.'],
                    ];
                    $sid = (int) $request->statusId;
                    if (isset($textos[$sid])) {
                        \Illuminate\Support\Facades\Mail::to($cliente->email)->send(
                            new \App\Mail\PedidoEstadoMail($order->fresh(), $cliente, $textos[$sid][0], $textos[$sid][1])
                        );
                    }
                }
            } catch (\Throwable $mailError) {
                \Illuminate\Support\Facades\Log::warning('No se pudo enviar el aviso de estado del pedido #' . $order->order_id . ': ' . $mailError->getMessage());
            }

            return response()->json([
                "status" => 1,
                "message" => "Se actualizó el status del pedido",
                "data" => $statusOrder
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'=> 0,
                'mensaje' => ['Excepción capturada: '.$th->getMessage()]
            ]);
        }
    }

    public function update_paid(Request $request)
    {
        $request->validate([
            'orderId'   => 'required|integer',
            'statusId'  => 'required|integer',
            'paymentId' => 'required|string', // "caja-12" o "banco-5"
        ]);

        DB::beginTransaction();
        try {
            $order = order_ecommerce::with(['detalles','cliente'])->findOrFail($request->orderId);

            // 🔹 Parsear destino de pago
            $cuentaId   = null;
            $aperturaId = null;
            $efectivo   = 0;
            $bancos     = 0;
            $tarjetas   = 0;

            if (str_starts_with($request->paymentId, 'caja-')) {
                $aperturaId = (int) str_replace('caja-', '', $request->paymentId);
                $apertura   = CajaApertura::findOrFail($aperturaId);

                if (!$apertura->estaAbierta()) {
                    return response()->json(['status' => 0, 'mensaje' => ['La caja seleccionada no está abierta.']]);
                }

                $cuentaId = $apertura->cuenta_id;
                $efectivo = $order->total_amount;
            } elseif (str_starts_with($request->paymentId, 'banco-')) {
                $cuentaId = (int) str_replace('banco-', '', $request->paymentId);
                $bancos   = $order->total_amount;
            }

            // 🔹 Registrar movimiento
            Movimiento::create([
                'cuenta_id'        => $cuentaId,
                'caja_apertura_id' => $aperturaId,
                'fecha'            => now(),
                'tipo'             => 'ingreso',
                'cliente_proveedor'=> optional($order->cliente)->nombre ?? 'Cliente ecommerce',
                'comprobante'      => 'Pedido #'.$order->order_id,
                'observaciones'    => 'Pago de pedido ecommerce',
                'efectivo'         => $efectivo,
                'bancos'           => $bancos,
                'tarjetas'         => $tarjetas,
                'total'            => $order->total_amount,
            ]);

            // 🔹 Actualizar estado de la orden
            $order->update(['status_order_id' => $request->statusId]);

            // 🔹 Actualizar método de pago
            $updatePayment = payment_ecommerce::where('order_id', $order->order_id)->first();
            $method = DB::table('payment_methods')
                ->where('method_name', $efectivo ? 'efectivo' : ($bancos ? 'banco' : 'tarjeta'))
                ->first();

            if ($updatePayment) {
                $updatePayment->payment_method_id = $method->payment_method_id;
                $updatePayment->save();
            }

            // 🔹 Descontar stock usando StockController
            $asignaciones = $order->asignaciones;
            foreach ($asignaciones as $asig) {
                app(StockController::class)->disminuirStockEnSucursal(
                    $asig->sucursal_id,
                    $asig->product_id,
                    $asig->cantidad,
                    $asig->combinacion_id
                );
            }

            DB::commit();
            return response()->json([
                "status"  => 1,
                "message" => "Éxito: El pedido se ha pagado y el stock fue descontado"
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'=> 0,
                'mensaje' => ['Excepción capturada: '.$th->getMessage()]
            ]);
        }
    }

    /**
     * Comprobante de pago del pedido (foto de transferencia, captura, PDF):
     * queda registrado junto al pedido.
     */
    public function subirComprobante(Request $request, $id)
    {
        $order = order_ecommerce::findOrFail($id);

        $request->validate([
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'nota'    => 'nullable|string|max:200',
        ], [
            'archivo.required' => 'Elegí la foto o el PDF del comprobante.',
            'archivo.mimes'    => 'Solo imágenes (JPG, PNG, WEBP) o PDF.',
            'archivo.max'      => 'Hasta 10 MB.',
        ]);

        $dir = public_path('imagenes/pedidos/comprobantes');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $nombre = 'pedido-' . $order->order_id . '-' . uniqid() . '.' . $request->file('archivo')->getClientOriginalExtension();
        $request->file('archivo')->move($dir, $nombre);

        DB::table('order_pago_comprobantes')->insert([
            'order_id' => $order->order_id,
            'archivo'  => 'imagenes/pedidos/comprobantes/' . $nombre,
            'mime'     => $request->file('archivo')->getClientMimeType(),
            'nota'     => $request->nota,
            'user_id'  => auth()->id(),
        ]);

        return back()->with('comp_ok', 'Comprobante registrado en el pedido.');
    }

    public function eliminarComprobante($compId)
    {
        $comp = DB::table('order_pago_comprobantes')->find($compId);
        if ($comp) {
            if (is_file(public_path($comp->archivo))) {
                @unlink(public_path($comp->archivo));
            }
            DB::table('order_pago_comprobantes')->delete($compId);
        }

        return response()->json(['status' => 1]);
    }

    public function show_order($id)
    {
        // 🔹 Traer la orden con todas sus relaciones
        $order = order_ecommerce::with([
            'cliente',
            'status',
            'detalles.producto',
            'detalles.combinacion',
            'pago'
        ])->findOrFail($id);

        // 🔹 Comprobantes de pago cargados (transferencias, capturas)
        $comprobantesPago = DB::table('order_pago_comprobantes')
            ->where('order_id', $id)
            ->orderByDesc('id')
            ->get();

        // 🔹 Preparar detalles (simple + combinaciones)
        $detailsOrder = $order->detalles->map(function($detalle) {
            $detalle->json_detalle = $detalle->combinacion ? $detalle->combinacion->json_detalle : null;
            $detalle->pcompra_variante = $detalle->combinacion->pcompra_variante ?? null;
            $detalle->pventa_variante = $detalle->combinacion->pventa_variante ?? null;
            return $detalle;
        });

        // 🔹 Método de pago si existe
        $namePayment = [];
        if ($order->pago && $order->pago->status_payment === 'Completado' && $order->pago->status == 1) {
            $namePayment = $order->pago->metodo()->select('method_name')->get();
        }

        // 🔹 Estados de la orden
        $currentStatusOrder = status_order_ecommerce::where('active', 1)->get();

        $statusOrder = $currentStatusOrder->map(function($ord) use ($order) {
            if ($order->status_order_id == $ord->status_id){
                $ord->action = 'complete';
                $ord->current_active = 'inactive';
            } else {
                if($ord->status_id > $order->status_order_id){
                    $ord->action = 'falta';
                    $ord->current_active = (($ord->status_id - 1) == $order->status_order_id) ? 'active' : 'inactive';

                    // 🚚 Pago contra entrega: con el stock comprobado se puede marcar
                    // "Enviado" sin pasar por "Pagado" — cobra el fletero en la puerta
                    // (el portal del fletero le muestra el pendiente exacto; si el
                    // cliente ya transfirió, le avisa que NO cobre).
                    if ($ord->status_id == 4 && $order->status_order_id == 2) {
                        $ord->current_active = 'active';
                    }
                }
                if($ord->status_id < $order->status_order_id){
                    $ord->action = 'complete';
                    $ord->current_active = 'inactive';
                }
            }

            // 🔹 Asignar ícono según el nombre del estado
            $ord->icon = match($ord->status_name) {
                'Pendiente' => 'fas fa-check',
                'Comprobación de stock' => 'fas fa-boxes',
                'Pagado' => 'fas fa-credit-card',
                'Enviado' => 'fas fa-truck',
                'Entregado' => ($order->status_order_id == 5) 
                    ? 'fas fa-check'   // ✅ palomita si ya está entregado
                    : 'fas fa-home',   // 🏠 casita mientras tanto
                'Cancelado' => 'fas fa-trash',
                default => 'fas fa-circle'
            };

            return $ord;
        });

        $typePayment = payment_methods::where('status',1)->get();

        return view('order.create', compact("detailsOrder","order","statusOrder","typePayment","namePayment","comprobantesPago"));
    }

}