<?php

namespace App\Http\Controllers\Venta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Articulo;
use App\Models\TipoComprobante;
use App\Models\CajaApertura;
use App\Models\Movimiento;
use App\Models\Iva;
use App\Models\Sucursal;
use App\Models\PriceListItem;
use App\Models\Revendedor;
use App\Models\RevendedorComision;
use App\Services\ChequeService;

use App\Http\Controllers\StockController;


use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class VentaController extends Controller
{
    /**
     * Vista principal del index de ventas
     */
    public function index()
    {
        // Tablero por estados (como el de fletes): a cobrar / cobradas / anuladas
        $base = fn () => Venta::with(['cliente', 'revendedor', 'movimientos.cuenta', 'tipoComprobante', 'sucursal']);

        $aCobrar = $base()->where('estado', 'a cobrar')
            ->orderByDesc('fecha')->orderByDesc('idventa')
            ->get();

        $cobradas = $base()->where('estado', 'cobrada')
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderByDesc('updated_at')->limit(30)
            ->get();

        $anuladas = $base()->where('estado', 'anulada')
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderByDesc('updated_at')->limit(15)
            ->get();

        return view('ventas.index', compact('aCobrar', 'cobradas', 'anuladas'));
    }

    /**
     * Mostrar formulario de creación de venta
     */
    public function create()
    {
        $clientes = Cliente::where('estatus', 'Activo')->orderBy('nombre')->get();
        $articulos = collect();
        $tiposComprobantes = TipoComprobante::operativos()->orderBy("idtipo_comprobante")->get();
        $ivas = Iva::all();
        $sucursales = Sucursal::all();
        $revendedores = Revendedor::where('estado', 'activo')->orderBy('nombre')->get();

        return view('ventas.create', compact(
            'clientes',
            'articulos',
            'tiposComprobantes',
            'ivas',
            'sucursales',
            'revendedores'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,idcliente',
            'fecha' => 'required|date',
            'tipo_comprobante_id' => 'required|exists:tipos_comprobantes,idtipo_comprobante',
            'items' => 'required|array|min:1',
            'items.*.idarticulo' => 'required|exists:productos,idarticulo',
            'items.*.combinacion_id' => 'nullable|exists:producto_combinaciones,idcombinacion',
            'items.*.tipo_producto_id' => 'required|integer|min:1',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.iva' => 'required|numeric|min:0',
            'items.*.descuento' => 'nullable|numeric|min:0|max:100',
            'items.*.price_list_id' => 'nullable|exists:price_lists,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'revendedor_id' => 'nullable|exists:revendedores,id',
            'tipo_venta' => 'nullable|in:minorista,mayorista',
        ]);

        DB::beginTransaction();
        try {
            // Crear la venta
            $venta = new Venta();
            $venta->user_id = auth()->id();
            $venta->cliente_id = $request->cliente_id;
            $venta->fecha = $request->fecha;
            $venta->tipo_comprobante_id = $request->tipo_comprobante_id;
            $venta->sucursal_id = $request->sucursal_id;
            $venta->revendedor_id = $request->revendedor_id ?: null;
            $venta->tipo_venta = $request->tipo_venta ?: 'minorista';
            $venta->estado = 'a cobrar';
            $venta->total_neto = 0;
            $venta->total_con_iva = 0;
            $venta->save();

            $totalNeto = 0;
            $totalConIva = 0;

            foreach ($request->items as $item) {
                $precioBase = $item['precio_unitario'];
                $precioFinal = $precioBase;

                // Ajustar según lista de precios
                if (!empty($item['price_list_id'])) {
                    $listItem = PriceListItem::where('price_list_id', $item['price_list_id'])
                        ->where('applicable_id', $item['idarticulo'])
                        ->first();

                    if ($listItem) {
                        $precioFinal = $listItem->getEffectivePrice($precioBase);
                    }
                }

                // Verificar y descontar stock
                app(StockController::class)->disminuirStockEnSucursal(
                    $request->sucursal_id,
                    $item['idarticulo'],
                    $item['cantidad'],
                    $item['combinacion_id'] ?? null
                );

                // aplicar descuento adicional
                $descuento = $item['descuento'] ?? 0;
                $precioConDescuento = $precioFinal - ($precioFinal * $descuento / 100);

                $subtotalNeto = $item['cantidad'] * $precioConDescuento;
                $montoIva = $subtotalNeto * ($item['iva'] / 100);
                $subtotalConIva = $subtotalNeto + $montoIva;

                $venta->detalles()->create([
                    'articulo_id'      => $item['idarticulo'],
                    'combinacion_id'   => $item['combinacion_id'] ?? null,
                    'tipo_producto_id' => $item['tipo_producto_id'],
                    'cantidad'         => $item['cantidad'],
                    'precio_unitario'  => $precioBase,
                    'price_list_id'    => $item['price_list_id'] ?? null,
                    'descuento'        => $descuento,
                    'iva'              => $item['iva'],
                    'subtotal_neto'    => $subtotalNeto,
                    'subtotal_con_iva' => $subtotalConIva,
                ]);

                $totalNeto += $subtotalNeto;
                $totalConIva += $subtotalConIva;
            }

            $venta->update([
                'total_neto' => $totalNeto,
                'total_con_iva' => $totalConIva,
            ]);

            // Comisión del revendedor: la venta manual ya está concretada,
            // entra directo como "aprobada" al circuito de liquidación.
            if ($request->revendedor_id) {
                $revendedor = Revendedor::find($request->revendedor_id);
                if ($revendedor) {
                    RevendedorComision::create([
                        'revendedor_id' => $revendedor->id,
                        'venta_id'      => $venta->idventa,
                        'monto_venta'   => $totalConIva,
                        'porcentaje'    => $revendedor->comision_porcentaje,
                        'comision'      => round($totalConIva * $revendedor->comision_porcentaje / 100, 2),
                        'estado'        => 'aprobada',
                    ]);
                }
            }

            DB::commit();

            \App\Models\Notificacion::avisar('venta',
                'Nueva venta ' . ($venta->fresh()->num_folio ?: '#' . $venta->idventa) . ' por $' . number_format($totalConIva, 0, ',', '.'),
                'Cliente: ' . trim(optional($venta->cliente)->nombre . ' ' . optional($venta->cliente)->paterno) . ($request->revendedor_id ? ' · vendió un revendedor' : ''),
                url('ventas?ver=' . $venta->idventa));

            return redirect()->route('ventas.index')->with('success', 'Venta registrada correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar la venta: '.$e->getMessage()]);
        }
    }

    public function anular($idventa)
    {
        $venta = Venta::findOrFail($idventa);
        $venta->estado = 'anulada';
        $venta->save();

        // Si la venta tenía comisión de revendedor sin pagar, se anula con ella
        RevendedorComision::where('venta_id', $idventa)
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->update(['estado' => 'anulada']);

        return response()->json(['success' => true]);
    }

    /**
     * Endpoint para DataTable server-side
     */
    public function list(Request $request)
    {
        $ventas = Venta::with(['cliente', 'tipoComprobante', 'sucursal']);

        return DataTables::of($ventas)
            ->addColumn('cliente', fn($venta) => $venta->cliente->nombre.' '.$venta->cliente->paterno)
            ->addColumn('telefono', fn($venta) => $venta->cliente->telefono ?? '')
            ->addColumn('tipo_comprobante', fn($venta) => $venta->tipoComprobante?->descripcion ?? '')
            ->addColumn('sucursal', fn($venta) => $venta->sucursal?->nombre ?? '-')
            ->editColumn('estado', function ($venta) {
                switch ($venta->estado) {
                    case 'a cobrar':
                        return '<span class="badge bg-success">A cobrar</span>';
                    case 'cobrada':
                        return '<span class="badge bg-success">Cobrada</span>';
                    case 'anulada':
                        return '<span class="badge bg-danger">Anulada</span>';
                    default:
                        return '<span class="badge bg-secondary">'.e($venta->estado).'</span>';
                }
            })
            ->addColumn('action', function ($venta) {
                $buttons = '
                    <button class="btn btn-sm btn-info" onclick="getDetailVenta('.$venta->idventa.')">
                        <i class="fa fa-eye"></i>
                    </button>
                ';

                if ($venta->estado === 'a cobrar') {
                    $buttons .= '
                        <button class="btn btn-sm btn-success" onclick="openPagoModal('.$venta->idventa.', '.$venta->sucursal_id.')">
                            <i class="fa fa-dollar-sign"></i>
                        </button>
                    ';
                }
                if ($venta->estado != 'anulada') {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger" onclick="anularVenta('.$venta->idventa.')">
                            <i class="fa fa-trash"></i>
                        </button>
                    ';
                }
                return $buttons;
            })
            ->rawColumns(['estado','action']) // importante para que se renderice el HTML
            ->make(true);
    }

    public function detail($idventa)
    {
        $venta = Venta::with([
            'cliente',
            'detalles.articulo.ivaVenta',
            'detalles.combinacion',
            'detalles.priceList',
            'tipoComprobante',
            'movimientos.cuenta'
        ])->findOrFail($idventa);

        // Cobros recibidos: a qué caja/banco entró cada uno, y cuánto falta
        $cobrado = (float) $venta->movimientos->sum('total');
        $pagos = $venta->movimientos->map(fn ($m) => [
            'fecha'  => \Carbon\Carbon::parse($m->fecha)->format('d/m/Y H:i'),
            'cuenta' => optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta',
            'monto'  => number_format($m->total, 2, ',', '.'),
        ])->values();

        $detalles = $venta->detalles->map(function($d) {
            return [
                'id_detalle' => $d->id_detalle,
                'articulo' => $d->articulo->nombre,
                'combinacion' => $d->combinacion ? $d->combinacion->combinacion : null, // ✅ nuevo campo
                'cantidad' => $d->cantidad,
                'precio_unitario' => number_format($d->precio_unitario, 2, ',', '.'),
                'price_list_id' => $d->price_list_id,
                'price_list_name' => $d->priceList ? $d->priceList->name : null,
                'descuento' => $d->descuento ?? 0,
                'iva' => $d->iva,
                'iva_label' => $d->articulo->ivaVenta ? $d->articulo->ivaVenta->tipo_iva : null,
                'subtotal_neto' => number_format($d->subtotal_neto, 2, ',', '.'),
                'subtotal_con_iva' => number_format($d->subtotal_con_iva, 2, ',', '.'),
                'subtotal_con_iva_raw' => (float) $d->subtotal_con_iva, // para calculos en JS (devoluciones/cambios)
            ];
        });

        return response()->json([
            'venta' => [
                'cliente' => $venta->cliente->nombre.' '.$venta->cliente->paterno.' '.$venta->cliente->materno,
                'fecha' => \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y'),
                'folio' => $venta->num_folio,
                'tipo_comprobante' => $venta->tipoComprobante ? $venta->tipoComprobante->descripcion : '',
                'total_neto' => number_format($venta->total_neto, 2, ',', '.'),
                'total_con_iva' => number_format($venta->total_con_iva, 2, ',', '.'),
                'total_con_iva_raw' => (float) $venta->total_con_iva,
                'iva_discriminado' => collect($venta->iva_discriminado)->map(function($monto, $porcentaje) {
                    return [
                        'porcentaje' => $porcentaje,
                        'monto' => number_format($monto, 2, ',', '.')
                    ];
                })->values()
            ],
            'detalles' => $detalles,
            'pagos' => $pagos,
            'cobrado' => number_format($cobrado, 2, ',', '.'),
            'pendiente' => number_format(max($venta->total_con_iva - $cobrado, 0), 2, ',', '.'),
            'tiene_pendiente' => $venta->estado === 'a cobrar' && ($venta->total_con_iva - $cobrado) > 0.009,
            'comprobantes' => DB::table('venta_pago_comprobantes')->where('venta_id', $venta->idventa)->orderByDesc('id')->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'url' => asset($c->archivo),
                    'es_imagen' => str_starts_with((string) $c->mime, 'image/'),
                    'nota' => $c->nota,
                    'fecha' => \Carbon\Carbon::parse($c->created_at)->format('d/m/Y'),
                ])->values(),
            'devoluciones' => \App\Models\Devolucion::where('tipo', 'venta')->where('referencia_id', $venta->idventa)
                ->orderByDesc('fecha')->get()
                ->map(fn ($d) => [
                    'resolucion' => $d->resolucion,
                    'producto_anterior' => $d->producto_anterior,
                    'producto_nuevo' => $d->producto_nuevo,
                    'diferencia' => number_format($d->diferencia, 2, ',', '.'),
                    'motivo' => $d->motivo,
                    'fecha' => \Carbon\Carbon::parse($d->fecha)->format('d/m/Y H:i'),
                ])->values(),
        ]);
    }

    /** Comprobante de pago de la venta (foto de transferencia, recibo, PDF). */
    public function subirComprobante(Request $request, $idventa)
    {
        $venta = Venta::findOrFail($idventa);

        $request->validate([
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'nota'    => 'nullable|string|max:200',
        ], [
            'archivo.required' => 'Elegí la foto o el PDF del comprobante.',
            'archivo.mimes'    => 'Solo imágenes (JPG, PNG, WEBP) o PDF.',
        ]);

        $dir = public_path('imagenes/ventas/comprobantes');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $nombre = 'venta-' . $venta->idventa . '-' . uniqid() . '.' . $request->file('archivo')->getClientOriginalExtension();
        $request->file('archivo')->move($dir, $nombre);

        DB::table('venta_pago_comprobantes')->insert([
            'venta_id' => $venta->idventa,
            'archivo'  => 'imagenes/ventas/comprobantes/' . $nombre,
            'mime'     => $request->file('archivo')->getClientMimeType(),
            'nota'     => $request->nota,
            'user_id'  => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function eliminarComprobante($compId)
    {
        $comp = DB::table('venta_pago_comprobantes')->find($compId);
        if ($comp) {
            if (is_file(public_path($comp->archivo))) {
                @unlink(public_path($comp->archivo));
            }
            DB::table('venta_pago_comprobantes')->delete($compId);
        }

        return response()->json(['success' => true]);
    }

    public function pendiente($idventa)
    {
        $venta = Venta::with(['movimientos.cuenta'])->findOrFail($idventa);

        $total = $venta->total_con_iva;
        $ingresado = $venta->movimientos()->sum('total');
        $pendiente = $total - $ingresado;

        return response()->json([
            'total_con_iva'   => round($total, 2),
            'monto_ingresado' => round($ingresado, 2),
            'monto_pendiente' => round($pendiente, 2),
            'sucursal_id'     => $venta->sucursal_id,
            'movimientos'     => $venta->movimientos->map(function ($mov) {
                return [
                    'id'        => $mov->id,
                    'cuenta'    => $mov->cuenta->nombre ?? null,
                    'tipo'      => $mov->cuenta->tipo ?? null, // caja o banco
                    'total'     => $mov->total,
                    'efectivo'  => $mov->efectivo,
                    'bancos'    => $mov->bancos,
                    'tarjetas'  => $mov->tarjetas,
                    'fecha'     => $mov->fecha->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }

    public function registrarPago(Request $request, $idventa, ChequeService $chequeService)
    {
        $request->validate([
            'cajas'   => 'required|array|min:1',
            'cajas.*' => 'required|string', // puede venir "caja-12" o "banco-5"
            'montos'  => 'required|array|min:1',
            'montos.*'=> 'numeric|min:0.01',
        ]);

        $venta = Venta::findOrFail($idventa);

        if ($venta->estado !== 'a cobrar') {
            return response()->json(['success' => false, 'error' => 'La venta no está disponible para cobro.']);
        }

        DB::beginTransaction();
        try {
            $total     = $venta->total_con_iva;
            $ingresado = $venta->movimientos()->sum('total');
            $pendiente = $total - $ingresado;

            $sumaNuevos = array_sum($request->input('montos', []));
            if ($sumaNuevos > $pendiente) {
                return response()->json(['success' => false, 'error' => 'El monto ingresado supera el pendiente.']);
            }

            foreach ($request->cajas as $index => $cuentaRef) {
                $monto = $request->montos[$index] ?? 0;
                if ($monto > 0) {
                    $cuentaId   = null;
                    $aperturaId = null;
                    $efectivo   = 0;
                    $bancos     = 0;
                    $tarjetas   = 0;
                    $tercero    = null;

                    if (str_starts_with($cuentaRef, 'caja-')) {
                        $aperturaId = (int) str_replace('caja-', '', $cuentaRef);
                        $apertura   = CajaApertura::findOrFail($aperturaId);

                        if (!$apertura->estaAbierta()) {
                            return response()->json(['success' => false, 'error' => 'La caja seleccionada no está abierta.']);
                        }

                        $cuentaId = $apertura->cuenta_id;
                        $efectivo = $monto;
                    } elseif (str_starts_with($cuentaRef, 'banco-')) {
                        $cuentaId = (int) str_replace('banco-', '', $cuentaRef);
                        $bancos   = $monto;
                    } elseif (str_starts_with($cuentaRef, 'tercero-')) {
                        $cuentaId = (int) str_replace('tercero-', '', $cuentaRef);
                        $bancos   = $monto; // transferencia a la cuenta de un tercero
                        // Alias dinámico: se registra en el movimiento a qué alias/CUIT fue la plata
                        $aliasTercero = trim((string) $request->input("alias_tercero.$index")) ?: null;
                        $cuitTercero  = trim((string) $request->input("cuit_tercero.$index")) ?: null;
                        if (!$aliasTercero) {
                            return response()->json(['success' => false, 'error' => 'Indicá el alias del tercero que recibió la transferencia.']);
                        }
                        $tercero = ['alias' => $aliasTercero, 'cuit' => $cuitTercero];
                    }

                    // El medio elegido define a qué columna va el monto:
                    // los cheques son papeles (no efectivo del cierre) y las
                    // tarjetas liquidan aparte — así el corte de caja distingue.
                    $medio = $request->input("medios.$index") ?: null;
                    $montoCheque = 0;
                    if ($medio) {
                        $efectivo = $bancos = $tarjetas = 0;
                        match (true) {
                            $medio === 'efectivo' => $efectivo = $monto,
                            in_array($medio, ['tarjeta_debito', 'tarjeta_credito']) => $tarjetas = $monto,
                            $medio === 'cheque' => $montoCheque = $monto,
                            default => $bancos = $monto, // transferencia, mercadopago, otro
                        };
                    }

                    if ($medio === 'cheque' && !$request->input("cheque_numero.$index")) {
                        return response()->json(['success' => false, 'error' => 'Indicá el número del cheque.']);
                    }

                    // 🔹 Un único create por iteración
                    $mov = Movimiento::create([
                        'cuenta_id'        => $cuentaId,
                        'caja_apertura_id' => $aperturaId,
                        'fecha'            => now(),
                        'tipo'             => 'ingreso',
                        'medio'            => $medio,
                        'alias_tercero'    => $tercero['alias'] ?? null,
                        'cuit_tercero'     => $tercero['cuit'] ?? null,
                        'cliente_proveedor'=> optional($venta->cliente)->nombre ?? 'Cliente',
                        'comprobante'      => $venta->num_folio,
                        'observaciones'    => 'Pago de venta registrado' . ($medio ? ' (' . str_replace('_', ' ', $medio) . ')' : '')
                            . ($tercero ? ' — transferido al alias ' . $tercero['alias'] . ($tercero['cuit'] ? ' (CUIT ' . $tercero['cuit'] . ')' : '') : ''),
                        'efectivo'         => $efectivo,
                        'bancos'           => $bancos,
                        'tarjetas'         => $tarjetas,
                        'cheques'          => $montoCheque,
                        'total'            => $monto,
                    ]);

                    if ($medio === 'cheque') {
                        $chequeService->registrarRecibido([
                            'numero'             => $request->input("cheque_numero.$index"),
                            'banco_emisor'       => $request->input("cheque_banco.$index"),
                            'contraparte_nombre' => $request->input("cheque_titular.$index") ?: (optional($venta->cliente)->nombre ?? 'Cliente'),
                            'monto'              => $monto,
                            'fecha_cobro'        => $request->input("cheque_fecha_cobro.$index") ?: now(),
                        ], $venta, $mov);
                    }
                }
            }

            $ingresadoFinal = $venta->movimientos()->sum('total');
            if ($ingresadoFinal >= $total) {
                $venta->estado = 'cobrada';
                $venta->save();

                \App\Models\Notificacion::avisar('cobro',
                    'Venta ' . ($venta->num_folio ?: '#' . $venta->idventa) . ' cobrada completa ($' . number_format($total, 0, ',', '.') . ')',
                    'Cliente: ' . trim(optional($venta->cliente)->nombre ?? ''),
                    url('ventas?ver=' . $venta->idventa), 'exito');
            }

            DB::commit();
            return response()->json([
                'success'   => true,
                'estado'    => $venta->estado,
                'pendiente' => max(0, $total - $ingresadoFinal)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Error al registrar el cobro: '.$e->getMessage()]);
        }
    }

}