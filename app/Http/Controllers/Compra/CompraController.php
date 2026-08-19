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


use Illuminate\Support\Facades\DB;
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
                'articulo' => $d->articulo->nombre,
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

    public function registrarPago(Request $request, $idcompra)
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
                if ($monto > 0) {
                    $cuentaId   = null;
                    $aperturaId = null;
                    $efectivo   = 0;
                    $bancos     = 0;
                    $tarjetas   = 0;

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
                    }

                    // 🔹 Un único create por iteración
                    $movimientosCreados[] = Movimiento::create([
                        'cuenta_id'        => $cuentaId,
                        'caja_apertura_id' => $aperturaId,
                        'fecha'            => now(),
                        'tipo'             => 'egreso', // salida de dinero
                        'cliente_proveedor'=> optional($compra->proveedor)->nombre ?? 'Proveedor',
                        'comprobante'      => $compra->num_folio,
                        'observaciones'    => 'Pago de compra registrado',
                        'efectivo'         => $efectivo,
                        'bancos'           => $bancos,
                        'tarjetas'         => $tarjetas,
                        'total'            => $monto,
                    ]);
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