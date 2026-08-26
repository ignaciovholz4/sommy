<?php

namespace App\Http\Controllers\Compra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Articulo;
use App\Models\TipoComprobante;
use App\Models\CajaApertura;
use App\Models\Movimiento;
use App\Models\Iva;
use App\Models\Sucursal;
use App\Models\PriceListItem;
use App\Models\ProveedorCcMovimiento;

use App\Http\Controllers\StockController;
use App\Models\CompraOcrExtraccion;
use App\Services\ChequeService;
use App\Services\Compras\ComprobanteOcrService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class CompraController extends Controller
{
    /**
     * Vista principal del index de compras
     */
    public function index()
    {
        // Tablero por estados (mismo patrón que el de ventas): a pagar / pagadas / anuladas
        $base = fn () => Compra::with(['proveedor', 'tipoComprobante', 'sucursal', 'movimientos', 'adjuntos']);

        $aPagar = $base()->where('estado', 'a pagar')
            ->orderByDesc('fecha')->orderByDesc('idcompra')
            ->get();

        $pagadas = $base()->where('estado', 'pagada')
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderByDesc('updated_at')->limit(30)
            ->get();

        $anuladas = $base()->where('estado', 'anulada')
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderByDesc('updated_at')->limit(15)
            ->get();

        return view('compras.index', compact('aPagar', 'pagadas', 'anuladas'));
    }

    /**
     * Mostrar formulario de creación de compra
     */
    public function create()
    {
        $proveedores = Proveedor::all();
        $articulos = collect();
        $tiposComprobantes = TipoComprobante::operativos()->orderBy("idtipo_comprobante")->get();
        $ivas = Iva::all();
        $sucursales = Sucursal::all();

        return view('compras.create', compact(
            'proveedores',
            'articulos',
            'tiposComprobantes',
            'ivas',
            'sucursales'
        ));
    }

    /**
     * Lee una factura/remito de proveedor subido con IA y devuelve los datos
     * extraidos + matches contra proveedor/articulos existentes, para
     * precompletar el formulario de alta. No crea la Compra: eso lo sigue
     * haciendo el usuario al confirmar y enviar el formulario normal.
     */
    public function ocrUpload(Request $request, ComprobanteOcrService $ocr)
    {
        Gate::authorize('haveaccess', 'compras.ocr_ia');

        $request->validate([
            'archivo' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:8192',
        ]);

        if (!$ocr->disponible()) {
            return response()->json(['success' => false, 'error' => 'La lectura con IA no esta configurada (falta GEMINI_API_KEY).'], 422);
        }

        $file = $request->file('archivo');
        $path = $file->store('compras/ocr-temp', 'local');
        $rutaAbsoluta = Storage::disk('local')->path($path);

        try {
            $extraido = $ocr->extraer($rutaAbsoluta, $file->getMimeType());
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'No se pudo leer el comprobante: ' . $e->getMessage()], 422);
        }

        $proveedor = $ocr->matchProveedor($extraido['proveedor_nombre'], $extraido['proveedor_cuit']);
        $tipoComprobante = $ocr->matchTipoComprobante($extraido['tipo_comprobante_sugerido']);

        $items = collect($extraido['items'])->map(function ($item) use ($ocr, $proveedor) {
            $articulo = $ocr->matchArticulo($item['descripcion'] ?? null, $item['codigo'] ?? null, $proveedor?->idproveedor);

            return [
                'descripcion_extraida' => $item['descripcion'] ?? null,
                'codigo_extraido' => $item['codigo'] ?? null,
                'cantidad' => $item['cantidad'] ?? null,
                'precio_unitario' => $item['precio_unitario'] ?? null,
                'idarticulo' => $articulo?->idarticulo,
                'nombre' => $articulo?->nombre,
                'codigo' => $articulo?->codigo,
                'tipo_producto_id' => $articulo?->tipo_producto_id,
                'pcompra_con_iva' => $articulo?->pcompra_con_iva,
                'iva_compra' => $articulo?->ivaCompra?->value_iva ?? 0,
                'descuento' => $articulo?->descuento ?? 0,
                'necesita_confirmacion' => $articulo === null,
            ];
        });

        $extraccion = CompraOcrExtraccion::create([
            'user_id' => auth()->id(),
            'archivo_path' => $path,
            'mime' => $file->getMimeType(),
            'proveedor_extraido' => $extraido['proveedor_nombre'],
            'proveedor_id_matched' => $proveedor?->idproveedor,
            'fecha_extraida' => $extraido['fecha'],
            'num_folio_extraido' => $extraido['num_folio'],
            'tipo_comprobante_sugerido' => $extraido['tipo_comprobante_sugerido'],
            'items_json' => $items->all(),
            'confianza' => $extraido['confianza_global'],
        ]);

        return response()->json([
            'success' => true,
            'extraccion_id' => $extraccion->id,
            'proveedor_id' => $proveedor?->idproveedor,
            'proveedor_necesita_confirmacion' => $proveedor === null,
            'proveedor_extraido' => $extraido['proveedor_nombre'],
            'fecha' => $extraido['fecha'],
            'num_folio_extraido' => $extraido['num_folio'],
            'tipo_comprobante_id' => $tipoComprobante?->idtipo_comprobante,
            'tipo_comprobante_necesita_confirmacion' => $tipoComprobante === null,
            'confianza' => $extraido['confianza_global'],
            'items' => $items->values(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,idproveedor',
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
        ]);

        DB::beginTransaction();
        try {
            // Crear la compra
            $compra = new Compra();
            $compra->user_id = auth()->id();
            $compra->proveedor_id = $request->proveedor_id;
            $compra->fecha = $request->fecha;
            $compra->tipo_comprobante_id = $request->tipo_comprobante_id;
            $compra->sucursal_id = $request->sucursal_id;
            $compra->estado = 'a pagar';
            $compra->total_neto = 0;
            $compra->total_con_iva = 0;
            $compra->save();

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

                // Incrementar stock en sucursal
                app(StockController::class)->incrementarStockEnSucursal(
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

                $compra->detalles()->create([
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

            // Actualizar totales de la compra
            $compra->update([
                'total_neto' => $totalNeto,
                'total_con_iva' => $totalConIva,
            ]);

            // 🔹 Compra a crédito: se genera la deuda en la cuenta corriente del proveedor (CxP).
            // Si algo de CxP falla, se loguea sin romper el alta de la compra.
            if ($request->boolean('a_credito')) {
                try {
                    $proveedor = Proveedor::find($request->proveedor_id);
                    $plazoDias = (int) ($proveedor->condicion_pago_dias ?? 0);

                    ProveedorCcMovimiento::create([
                        'proveedor_id'      => $compra->proveedor_id,
                        'tipo'              => 'debe',
                        'origen'            => 'compra',
                        'compra_id'         => $compra->idcompra,
                        'monto'             => $totalConIva,
                        'fecha_vencimiento' => \Carbon\Carbon::parse($request->fecha)->addDays($plazoDias)->toDateString(),
                        'estado'            => 'pendiente',
                        'descripcion'       => 'Compra #' . $compra->idcompra . ($compra->num_folio ? ' (' . $compra->num_folio . ')' : ''),
                        'user_id'           => auth()->id(),
                    ]);
                } catch (\Throwable $ccError) {
                    \Illuminate\Support\Facades\Log::warning('No se pudo generar la deuda de CxP para la compra #' . $compra->idcompra . ': ' . $ccError->getMessage());
                }
            }

            DB::commit();

            return redirect()->route('compras.index')->with('success', 'Compra registrada correctamente');
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar la compra: '.$e->getMessage()]);
        }
    }

    public function anular($idcompra)
    {
        $compra = Compra::findOrFail($idcompra);
        $compra->estado = 'anulada';
        $compra->save();

        return response()->json(['success' => true]);
    }

    /**
     * Endpoint para DataTable server-side
     */
    public function list(Request $request)
    {
        $compras = Compra::with(['proveedor', 'tipoComprobante', 'sucursal']);

        return DataTables::of($compras)
            ->addColumn('proveedor', fn($compra) => $compra->proveedor->nombre)
            ->addColumn('telefono', fn($compra) => $compra->proveedor->telefono ?? '')
            ->addColumn('tipo_comprobante', fn($compra) => $compra->tipoComprobante?->descripcion ?? '')
            ->addColumn('sucursal', fn($compra) => $compra->sucursal?->nombre ?? '-')
            ->editColumn('estado', function ($compra) {
                switch ($compra->estado) {
                    case 'a pagar':
                        return '<span class="badge bg-success">A pagar</span>';
                    case 'pagada':
                        return '<span class="badge bg-success">Pagada</span>';
                    case 'anulada':
                        return '<span class="badge bg-danger">Anulada</span>';
                    default:
                        return '<span class="badge bg-secondary">'.e($compra->estado).'</span>';
                }
            })
            ->addColumn('action', function ($compra) {
                $buttons = '
                    <button class="btn btn-sm btn-info" onclick="getDetailCompra('.$compra->idcompra.')">
                        <i class="fa fa-eye"></i>
                    </button>
                ';
                if ($compra->estado === 'a pagar') {
                    $buttons .= '
                        <button class="btn btn-sm btn-success" onclick="openPagoModalCompra('.$compra->idcompra.', '.$compra->sucursal_id.')">
                            <i class="fa fa-dollar-sign"></i>
                        </button>
                    ';
                }
                if ($compra->estado != 'anulada') {
                    $buttons .= '
                        <button class="btn btn-sm btn-danger" onclick="anularCompra('.$compra->idcompra.')">
                            <i class="fa fa-trash"></i>
                        </button>
                    ';
                }
                return $buttons;
            })
            ->rawColumns(['estado','action']) // importante para renderizar HTML
            ->make(true);
    }

    public function detail($idcompra)
    {
        $compra = Compra::with([
            'proveedor',
            'detalles.articulo.ivaCompra',
            'detalles.combinacion',
            'detalles.priceList',
            'tipoComprobante',
            'adjuntos',
            'movimientos.cuenta'
        ])->findOrFail($idcompra);

        // Pagos realizados: a qué caja/banco fue cada uno, y cuánto falta
        $pagado = (float) $compra->movimientos->sum('total');
        $pagos = $compra->movimientos->map(fn ($m) => [
            'fecha'  => \Carbon\Carbon::parse($m->fecha)->format('d/m/Y H:i'),
            'cuenta' => optional($m->cuenta ?? optional($m->cajaApertura)->cuenta)->nombre ?: 'Cuenta',
            'monto'  => number_format($m->total, 2, ',', '.'),
        ])->values();

        $detalles = $compra->detalles->map(function($d) {
            return [
                'articulo' => $d->articulo->nombre_compra ?: $d->articulo->nombre,
                'combinacion' => $d->combinacion ? $d->combinacion->combinacion : null, // ✅ nuevo campo
                'cantidad' => $d->cantidad,
                'precio_unitario' => number_format($d->precio_unitario, 2, ',', '.'),
                'price_list_id' => $d->price_list_id,
                'price_list_name' => $d->priceList ? $d->priceList->name : null,
                'descuento' => $d->descuento ?? 0,
                'iva' => $d->iva,
                'iva_label' => $d->articulo->ivaCompra ? $d->articulo->ivaCompra->tipo_iva : null,
                'subtotal_neto' => number_format($d->subtotal_neto, 2, ',', '.'),
                'subtotal_con_iva' => number_format($d->subtotal_con_iva, 2, ',', '.'),
            ];
        });

        return response()->json([
            'compra' => [
                'proveedor' => $compra->proveedor->nombre,
                'fecha' => \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y'),
                'folio' => $compra->num_folio,
                'tipo_comprobante' => $compra->tipoComprobante ? $compra->tipoComprobante->descripcion : '',
                'total_neto' => number_format($compra->total_neto, 2, ',', '.'),
                'total_con_iva' => number_format($compra->total_con_iva, 2, ',', '.'),
                'iva_discriminado' => collect($compra->iva_discriminado)->map(function($monto, $porcentaje) {
                    return [
                        'porcentaje' => $porcentaje,
                        'monto' => number_format($monto, 2, ',', '.')
                    ];
                })->values()
            ],
            'detalles' => $detalles,
            'pagos' => $pagos,
            'pagado' => number_format($pagado, 2, ',', '.'),
            'pendiente' => number_format(max($compra->total_con_iva - $pagado, 0), 2, ',', '.'),
            'tiene_pendiente' => $compra->estado === 'a pagar' && ($compra->total_con_iva - $pagado) > 0.009,
            'adjuntos' => $compra->adjuntos->map(function($a) {
                return [
                    'url' => $a->url,
                    'name' => $a->original_name,
                    'es_imagen' => $a->es_imagen,
                ];
            })->values(),
        ]);
    }

    public function pendiente($idcompra)
    {
        $compra = Compra::with(['movimientos.cuenta'])->findOrFail($idcompra);

        $total = $compra->total_con_iva;

        // Sumamos todos los movimientos asociados a la compra
        $pagado = $compra->movimientos()->sum('total');
        $pendiente = $total - $pagado;

        return response()->json([
            'total_con_iva'   => round($total, 2),
            'monto_ingresado' => round($pagado, 2),
            'monto_pendiente' => round($pendiente, 2),
            'sucursal_id'     => $compra->sucursal_id,
            'movimientos'     => $compra->movimientos->map(function ($mov) {
                return [
                    'id'        => $mov->id,
                    'cuenta'    => $mov->cuenta->nombre ?? null,
                    'tipo'      => $mov->cuenta->tipo ?? null, // caja o banco
                    'total'     => $mov->total,
                    'efectivo'  => $mov->efectivo,
                    'bancos'    => $mov->bancos,
                    'tarjetas'  => $mov->tarjetas,
                    'fecha'     => $mov->fecha instanceof \Carbon\Carbon
                                    ? $mov->fecha->format('d/m/Y H:i')
                                    : $mov->fecha,
                ];
            }),
        ]);
    }

    public function registrarPago(Request $request, $idcompra, ChequeService $chequeService)
    {
        $request->validate([
            'cajas'   => 'required|array|min:1',
            'cajas.*' => 'required|string', // puede venir "caja-12" o "banco-5"
            'montos'  => 'required|array|min:1',
            'montos.*'=> 'numeric|min:0.01',
        ]);

        $compra = Compra::findOrFail($idcompra);

        if ($compra->estado !== 'a pagar') {
            return response()->json(['success' => false, 'error' => 'La compra no está disponible para pago.']);
        }

        DB::beginTransaction();
        try {
            $total     = $compra->total_con_iva;
            $pagado    = $compra->movimientos()->sum('total');
            $pendiente = $total - $pagado;

            $sumaNuevos = array_sum($request->input('montos', []));
            if ($sumaNuevos > $pendiente) {
                return response()->json(['success' => false, 'error' => 'El monto ingresado supera el pendiente.']);
            }

            $movimientosCreados = [];

            foreach ($request->cajas as $index => $cuentaRef) {
                $monto = $request->montos[$index] ?? 0;

                // Endoso: se paga entregando un cheque de tercero que ya está en cartera.
                $chequeEndosado = str_starts_with($cuentaRef, 'cheque-')
                    ? $chequeService->resolverEndoso($cuentaRef)
                    : null;
                if (str_starts_with($cuentaRef, 'cheque-') && !$chequeEndosado) {
                    return response()->json(['success' => false, 'error' => 'Ese cheque ya no está disponible para entregar.']);
                }
                if ($chequeEndosado) {
                    $monto = (float) $chequeEndosado->monto;
                }

                if ($monto > 0) {
                    $cuentaId   = null;
                    $aperturaId = null;
                    $efectivo   = 0;
                    $bancos     = 0;
                    $tarjetas   = 0;

                    $montoCheque = 0;

                    if ($chequeEndosado) {
                        // Sin cuenta interna: el cheque sale de cartera, no de caja/banco.
                        $montoCheque = $monto;
                    } elseif (str_starts_with($cuentaRef, 'caja-')) {
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
                    }

                    // El medio elegido define a qué columna va el monto (los cheques
                    // entregados no salen del efectivo del cierre de caja)
                    $medio = $chequeEndosado ? 'cheque' : ($request->input("medios.$index") ?: null);
                    if ($medio && !$chequeEndosado) {
                        $efectivo = $bancos = $tarjetas = 0;
                        match (true) {
                            $medio === 'efectivo' => $efectivo = $monto,
                            in_array($medio, ['tarjeta_debito', 'tarjeta_credito']) => $tarjetas = $monto,
                            $medio === 'cheque' => $montoCheque = $monto,
                            default => $bancos = $monto, // transferencia, mercadopago, otro
                        };
                    }

                    if ($medio === 'cheque' && !$chequeEndosado && !$request->input("cheque_numero.$index")) {
                        return response()->json(['success' => false, 'error' => 'Indicá el número del cheque.']);
                    }

                    // 🔹 Un único create por iteración
                    $mov = Movimiento::create([
                        'cuenta_id'        => $cuentaId,
                        'caja_apertura_id' => $aperturaId,
                        'fecha'            => now(),
                        'tipo'             => 'egreso', // salida de dinero
                        'medio'            => $medio,
                        'cliente_proveedor'=> optional($compra->proveedor)->nombre ?? 'Proveedor',
                        'comprobante'      => $compra->num_folio,
                        'observaciones'    => 'Pago de compra registrado' . ($medio ? ' (' . str_replace('_', ' ', $medio) . ')' : '')
                            . ($chequeEndosado ? ' — entregado cheque Nº ' . $chequeEndosado->numero : ''),
                        'efectivo'         => $efectivo,
                        'bancos'           => $bancos,
                        'tarjetas'         => $tarjetas,
                        'cheques'          => $montoCheque,
                        'total'            => $monto,
                    ]);

                    $movimientosCreados[] = $mov;

                    if ($chequeEndosado) {
                        $chequeService->entregar($chequeEndosado, $mov);
                    } elseif ($medio === 'cheque') {
                        $chequeService->registrarPropio([
                            'numero'             => $request->input("cheque_numero.$index"),
                            'banco_emisor'       => $request->input("cheque_banco.$index"),
                            'contraparte_nombre' => $request->input("cheque_titular.$index") ?: (optional($compra->proveedor)->nombre ?? 'Proveedor'),
                            'monto'              => $monto,
                            'fecha_cobro'        => $request->input("cheque_fecha_cobro.$index") ?: now(),
                        ], $compra, $mov);
                    }
                }
            }

            $pagadoFinal = $compra->movimientos()->sum('total');
            if ($pagadoFinal >= $total) {
                $compra->estado = 'pagada';
                $compra->save();
            }

            // 🔹 Si la compra tiene deuda en CxP (fue a crédito), se registra el haber
            // vinculado a cada movimiento y se reimputa FIFO. Si algo de CxP falla,
            // se loguea sin romper el flujo de pago existente.
            try {
                $tieneDeudaCxp = ProveedorCcMovimiento::where('compra_id', $compra->idcompra)
                    ->where('tipo', 'debe')
                    ->exists();

                if ($tieneDeudaCxp && count($movimientosCreados) > 0) {
                    // Deuda restante de esta compra en CC (debe - haber ya imputado a la compra)
                    $debeCompra  = (float) ProveedorCcMovimiento::where('compra_id', $compra->idcompra)->where('tipo', 'debe')->sum('monto');
                    $haberCompra = (float) ProveedorCcMovimiento::where('compra_id', $compra->idcompra)->where('tipo', 'haber')->sum('monto');
                    $restante    = max(0, $debeCompra - $haberCompra);

                    foreach ($movimientosCreados as $mov) {
                        if ($restante <= 0) {
                            break;
                        }

                        $montoHaber = min((float) $mov->total, $restante);

                        ProveedorCcMovimiento::create([
                            'proveedor_id'  => $compra->proveedor_id,
                            'tipo'          => 'haber',
                            'origen'        => 'pago',
                            'compra_id'     => $compra->idcompra,
                            'movimiento_id' => $mov->id,
                            'monto'         => $montoHaber,
                            'descripcion'   => 'Pago compra #' . $compra->idcompra . ($compra->num_folio ? ' (' . $compra->num_folio . ')' : ''),
                            'user_id'       => auth()->id(),
                        ]);

                        $restante -= $montoHaber;
                    }

                    ProveedorCcMovimiento::reimputarFifo((int) $compra->proveedor_id);
                }
            } catch (\Throwable $ccError) {
                \Illuminate\Support\Facades\Log::warning('No se pudo imputar el pago en CxP para la compra #' . $compra->idcompra . ': ' . $ccError->getMessage());
            }

            DB::commit();
            return response()->json([
                'success'   => true,
                'estado'    => $compra->estado,
                'pendiente' => max(0, $total - $pagadoFinal)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Error al registrar el pago: '.$e->getMessage()]);
        }
    }

}