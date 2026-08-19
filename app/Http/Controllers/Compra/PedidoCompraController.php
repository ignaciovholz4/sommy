<?php

namespace App\Http\Controllers\Compra;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StockController;
use App\Models\Compra;
use App\Models\Iva;
use App\Models\PedidoCompra;
use App\Models\PriceListItem;
use App\Models\Proveedor;
use App\Models\ProveedorCcMovimiento;
use App\Models\Sucursal;
use App\Models\TipoComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Generador de pedidos de compra (órdenes de compra borrador).
 * Un pedido NO afecta stock, caja ni cuentas por pagar: recién al
 * convertirlo en compra se ejecuta la lógica real de CompraController::store.
 */
class PedidoCompraController extends Controller
{
    public function index()
    {
        return view('pedidos_compra.index');
    }

    public function create()
    {
        $proveedores = Proveedor::all();
        $tiposComprobantes = TipoComprobante::operativos()->orderBy("idtipo_comprobante")->get();
        $ivas = Iva::all();
        $sucursales = Sucursal::all();

        return view('pedidos_compra.create', compact(
            'proveedores',
            'tiposComprobantes',
            'ivas',
            'sucursales'
        ));
    }

    public function store(Request $request)
    {
        $this->validarPayload($request);

        DB::beginTransaction();
        try {
            $pedido = new PedidoCompra();
            $pedido->user_id = auth()->id();
            $pedido->proveedor_id = $request->proveedor_id;
            $pedido->fecha = $request->fecha;
            $pedido->tipo_comprobante_id = $request->tipo_comprobante_id;
            $pedido->sucursal_id = $request->sucursal_id;
            $pedido->estado = 'borrador';
            $pedido->a_credito = $request->boolean('a_credito');
            $pedido->observaciones = $request->observaciones;
            $pedido->total_neto = 0;
            $pedido->total_con_iva = 0;
            $pedido->save();

            // Folio correlativo tipo PC-000001
            $pedido->num_folio = 'PC-' . str_pad($pedido->id, 6, '0', STR_PAD_LEFT);
            $pedido->save();

            $this->guardarDetalles($pedido, $request->items);

            DB::commit();

            return redirect()->route('pedidos-compra.index')->with('success', 'Pedido de compra creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el pedido: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $pedido = PedidoCompra::with([
            'detalles.articulo',
            'detalles.combinacion',
            'detalles.priceList',
        ])->findOrFail($id);

        if ($pedido->estado !== 'borrador') {
            return redirect()->route('pedidos-compra.index')
                ->withErrors(['error' => 'Solo se pueden editar pedidos en estado borrador.']);
        }

        $proveedores = Proveedor::all();
        $tiposComprobantes = TipoComprobante::operativos()->orderBy("idtipo_comprobante")->get();
        $ivas = Iva::all();
        $sucursales = Sucursal::all();

        return view('pedidos_compra.edit', compact(
            'pedido',
            'proveedores',
            'tiposComprobantes',
            'ivas',
            'sucursales'
        ));
    }

    public function update(Request $request, $id)
    {
        $pedido = PedidoCompra::findOrFail($id);

        if ($pedido->estado !== 'borrador') {
            return back()->withErrors(['error' => 'Solo se pueden editar pedidos en estado borrador.']);
        }

        $this->validarPayload($request);

        DB::beginTransaction();
        try {
            $pedido->update([
                'proveedor_id' => $request->proveedor_id,
                'fecha' => $request->fecha,
                'tipo_comprobante_id' => $request->tipo_comprobante_id,
                'sucursal_id' => $request->sucursal_id,
                'a_credito' => $request->boolean('a_credito'),
                'observaciones' => $request->observaciones,
                'total_neto' => 0,
                'total_con_iva' => 0,
            ]);

            $pedido->detalles()->delete();
            $this->guardarDetalles($pedido, $request->items);

            DB::commit();

            return redirect()->route('pedidos-compra.index')->with('success', 'Pedido de compra actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el pedido: ' . $e->getMessage()]);
        }
    }

    /**
     * Endpoint para DataTable server-side
     */
    public function list(Request $request)
    {
        $pedidos = PedidoCompra::with(['proveedor', 'tipoComprobante', 'sucursal', 'compra']);

        return DataTables::of($pedidos)
            ->editColumn('num_folio', function ($p) {
                $badge = $p->origen === 'ia_reposicion'
                    ? ' <span class="badge bg-info text-dark" title="Generado automáticamente por reposición inteligente"><i class="fas fa-robot"></i> IA</span>'
                    : '';
                return e($p->num_folio) . $badge;
            })
            ->addColumn('proveedor', fn($p) => $p->proveedor->nombre)
            ->addColumn('tipo_comprobante', fn($p) => $p->tipoComprobante?->descripcion ?? '')
            ->addColumn('sucursal', fn($p) => $p->sucursal?->nombre ?? '-')
            ->editColumn('estado', function ($p) {
                switch ($p->estado) {
                    case 'borrador':
                        return '<span class="badge bg-warning text-dark">Borrador</span>';
                    case 'convertido':
                        $folio = $p->compra?->num_folio ? ' (' . e($p->compra->num_folio) . ')' : '';
                        return '<span class="badge bg-success">Convertido' . $folio . '</span>';
                    case 'anulado':
                        return '<span class="badge bg-danger">Anulado</span>';
                    default:
                        return '<span class="badge bg-secondary">' . e($p->estado) . '</span>';
                }
            })
            ->addColumn('action', function ($p) {
                $buttons = '
                    <button class="btn btn-sm btn-info" onclick="getDetailPedido(' . $p->id . ')" title="Ver detalle">
                        <i class="fa fa-eye"></i>
                    </button>
                    <a href="' . route('pedidos-compra.pdf', $p->id) . '" class="btn btn-sm btn-secondary" title="Descargar PDF">
                        <i class="fa fa-file-pdf"></i>
                    </a>
                ';
                if ($p->estado === 'borrador') {
                    $buttons .= '
                        <a href="' . route('pedidos-compra.edit', $p->id) . '" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-success" onclick="convertirPedido(' . $p->id . ', ' . ($p->a_credito ? 'true' : 'false') . ')" title="Convertir en compra">
                            <i class="fa fa-cart-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="anularPedido(' . $p->id . ')" title="Anular">
                            <i class="fa fa-trash"></i>
                        </button>
                    ';
                }
                return $buttons;
            })
            ->rawColumns(['num_folio', 'estado', 'action'])
            ->make(true);
    }

    public function detail($id)
    {
        $pedido = PedidoCompra::with([
            'proveedor',
            'detalles.articulo.ivaCompra',
            'detalles.combinacion',
            'detalles.priceList',
            'sucursal',
            'compra.adjuntos',
        ])->findOrFail($id);

        $detalles = $pedido->detalles->map(function ($d) {
            return [
                'articulo' => $d->articulo->nombre,
                'combinacion' => $d->combinacion ? $d->combinacion->combinacion : null,
                'cantidad' => $d->cantidad,
                'precio_unitario' => number_format($d->precio_unitario, 2, ',', '.'),
                'price_list_name' => $d->priceList ? $d->priceList->name : null,
                'descuento' => $d->descuento ?? 0,
                'iva' => $d->iva,
                'iva_label' => $d->articulo->ivaCompra ? $d->articulo->ivaCompra->tipo_iva : null,
                'subtotal_neto' => number_format($d->subtotal_neto, 2, ',', '.'),
                'subtotal_con_iva' => number_format($d->subtotal_con_iva, 2, ',', '.'),
            ];
        });

        return response()->json([
            'pedido' => [
                'proveedor' => $pedido->proveedor->nombre,
                'fecha' => \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y'),
                'folio' => $pedido->num_folio,
                'tipo_comprobante' => $pedido->tipoComprobante?->descripcion ?? '-',
                'sucursal' => $pedido->sucursal?->nombre ?? '-',
                'estado' => $pedido->estado,
                'a_credito' => $pedido->a_credito,
                'observaciones' => $pedido->observaciones,
                'compra_folio' => $pedido->compra?->num_folio,
                'total_neto' => number_format($pedido->total_neto, 2, ',', '.'),
                'total_con_iva' => number_format($pedido->total_con_iva, 2, ',', '.'),
                'iva_discriminado' => collect($pedido->iva_discriminado)->map(function ($monto, $porcentaje) {
                    return [
                        'porcentaje' => $porcentaje,
                        'monto' => number_format($monto, 2, ',', '.'),
                    ];
                })->values(),
            ],
            'detalles' => $detalles,
            // Comprobantes adjuntados al convertir el pedido en compra (remito, factura)
            'adjuntos' => optional($pedido->compra)->adjuntos?->map(fn ($a) => [
                'url' => $a->url,
                'name' => $a->original_name,
                'es_imagen' => $a->es_imagen,
            ])->values() ?? [],
        ]);
    }

    /**
     * Descarga el pedido de compra como PDF (orden de compra para el proveedor).
     */
    public function pdf($id)
    {
        $pedido = PedidoCompra::with([
            'proveedor',
            'detalles.articulo',
            'detalles.combinacion',
            'sucursal',
            'tipoComprobante',
            'user',
        ])->findOrFail($id);

        $detalle = $pedido->detalles->map(function ($d) {
            $nombre = $d->articulo->nombre;
            if ($d->combinacion) {
                $nombre .= ' - ' . $d->combinacion->combinacion;
            }

            return [
                'codigo'           => $d->articulo->codigo ?? '',
                'nombre'           => $nombre,
                'cantidad'         => $d->cantidad,
                'precio_unitario'  => number_format($d->precio_unitario, 2, ',', '.'),
                'descuento'        => $d->descuento ?? 0,
                'iva'              => $d->iva,
                'subtotal_neto'    => number_format($d->subtotal_neto, 2, ',', '.'),
                'subtotal_con_iva' => number_format($d->subtotal_con_iva, 2, ',', '.'),
            ];
        });

        $data = [
            'folio'            => $pedido->num_folio,
            'fecha'            => \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y'),
            'estado'           => ucfirst($pedido->estado),
            'a_credito'        => $pedido->a_credito,
            'proveedor'        => $pedido->proveedor->nombre,
            'proveedor_tel'    => $pedido->proveedor->telefono ?? '',
            'proveedor_email'  => $pedido->proveedor->email ?? '',
            'proveedor_dir'    => $pedido->proveedor->direccion ?? '',
            'tipo_comprobante' => $pedido->tipoComprobante?->descripcion ?? '-',
            'sucursal'         => $pedido->sucursal?->nombre ?? '-',
            'solicitante'      => $pedido->user?->name ?? '',
            'observaciones'    => $pedido->observaciones,
            'total_neto'       => number_format($pedido->total_neto, 2, ',', '.'),
            'total_con_iva'    => number_format($pedido->total_con_iva, 2, ',', '.'),
            'iva_discriminado' => collect($pedido->iva_discriminado)->map(function ($monto, $porcentaje) {
                return [
                    'porcentaje' => $porcentaje,
                    'monto' => number_format($monto, 2, ',', '.'),
                ];
            })->values(),
            'detalle'          => $detalle,
        ];

        $pdf = \PDF::loadView('pedidos_compra.pdf', $data);

        return $pdf->download('pedido_compra_' . $pedido->num_folio . '.pdf');
    }

    public function anular($id)
    {
        $pedido = PedidoCompra::findOrFail($id);

        if ($pedido->estado !== 'borrador') {
            return response()->json(['error' => 'Solo se pueden anular pedidos en estado borrador.'], 400);
        }

        $pedido->estado = 'anulado';
        $pedido->save();

        return response()->json(['success' => true]);
    }

    /**
     * Convierte el pedido en una compra real: crea la Compra, suma stock
     * y genera la deuda de CxP si el pedido era a crédito.
     */
    public function convertir(Request $request, $id)
    {
        $pedido = PedidoCompra::with('detalles')->findOrFail($id);

        if ($pedido->estado !== 'borrador') {
            return response()->json(['error' => 'El pedido ya fue convertido o está anulado.'], 400);
        }

        if ($pedido->detalles->isEmpty()) {
            return response()->json(['error' => 'El pedido no tiene artículos.'], 400);
        }

        $request->validate([
            'adjuntos'   => 'nullable|array|max:10',
            'adjuntos.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:8192',
        ], [
            'adjuntos.*.mimes' => 'Los adjuntos deben ser imágenes (JPG, PNG, WEBP) o PDF.',
            'adjuntos.*.max'   => 'Cada adjunto puede pesar como máximo 8 MB.',
        ]);

        // Permite decidir la condición de pago al momento de convertir
        $aCredito = $request->has('a_credito') ? $request->boolean('a_credito') : $pedido->a_credito;

        $rutasGuardadas = [];

        DB::beginTransaction();
        try {
            $compra = new Compra();
            $compra->user_id = auth()->id();
            $compra->proveedor_id = $pedido->proveedor_id;
            $compra->fecha = now()->toDateString();
            $compra->tipo_comprobante_id = $pedido->tipo_comprobante_id;
            $compra->sucursal_id = $pedido->sucursal_id;
            $compra->estado = 'a pagar';
            $compra->total_neto = $pedido->total_neto;
            $compra->total_con_iva = $pedido->total_con_iva;
            $compra->save();

            // El num_folio (CMP-xxxxxx) lo asigna el trigger de la tabla
            $compra->refresh();

            foreach ($pedido->detalles as $detalle) {
                app(StockController::class)->incrementarStockEnSucursal(
                    $pedido->sucursal_id,
                    $detalle->articulo_id,
                    $detalle->cantidad,
                    $detalle->combinacion_id ?? null
                );

                $compra->detalles()->create([
                    'articulo_id'      => $detalle->articulo_id,
                    'combinacion_id'   => $detalle->combinacion_id,
                    'tipo_producto_id' => $detalle->tipo_producto_id,
                    'cantidad'         => $detalle->cantidad,
                    'precio_unitario'  => $detalle->precio_unitario,
                    'price_list_id'    => $detalle->price_list_id,
                    'descuento'        => $detalle->descuento ?? 0,
                    'iva'              => $detalle->iva,
                    'subtotal_neto'    => $detalle->subtotal_neto,
                    'subtotal_con_iva' => $detalle->subtotal_con_iva,
                ]);
            }

            // Compra a crédito: deuda en cuenta corriente del proveedor
            if ($aCredito) {
                $proveedor = Proveedor::find($pedido->proveedor_id);
                $plazoDias = (int) ($proveedor->condicion_pago_dias ?? 0);

                ProveedorCcMovimiento::create([
                    'proveedor_id'      => $compra->proveedor_id,
                    'tipo'              => 'debe',
                    'origen'            => 'compra',
                    'compra_id'         => $compra->idcompra,
                    'monto'             => $compra->total_con_iva,
                    'fecha_vencimiento' => \Carbon\Carbon::parse($compra->fecha)->addDays($plazoDias)->toDateString(),
                    'estado'            => 'pendiente',
                    'descripcion'       => 'Compra #' . $compra->idcompra . ($compra->num_folio ? ' (' . $compra->num_folio . ')' : '') . ' - Pedido ' . $pedido->num_folio,
                    'user_id'           => auth()->id(),
                ]);
            }

            // Adjuntos del comprobante (remito, factura, etc.)
            foreach ($request->file('adjuntos', []) as $archivo) {
                $path = $archivo->store('compras/adjuntos', 'public');
                $rutasGuardadas[] = $path;

                $compra->adjuntos()->create([
                    'path'          => $path,
                    'original_name' => $archivo->getClientOriginalName(),
                    'mime'          => $archivo->getMimeType(),
                    'size'          => $archivo->getSize(),
                    'user_id'       => auth()->id(),
                ]);
            }

            // El pedido queda trazado, no se borra
            $pedido->update([
                'estado' => 'convertido',
                'compra_id' => $compra->idcompra,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'compra_id' => $compra->idcompra,
                'num_folio' => $compra->num_folio,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Los archivos no son transaccionales: limpiamos los que quedaron huérfanos
            foreach ($rutasGuardadas as $ruta) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($ruta);
            }
            return response()->json(['error' => 'Error al convertir el pedido: ' . $e->getMessage()], 500);
        }
    }

    private function validarPayload(Request $request): void
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedores,idproveedor',
            'fecha' => 'required|date',
            'tipo_comprobante_id' => 'required|exists:tipos_comprobantes,idtipo_comprobante',
            'sucursal_id' => 'required|exists:sucursales,id',
            'observaciones' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.idarticulo' => 'required|exists:productos,idarticulo',
            'items.*.combinacion_id' => 'nullable|exists:producto_combinaciones,idcombinacion',
            'items.*.tipo_producto_id' => 'required|integer|min:1',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.iva' => 'required|numeric|min:0',
            'items.*.descuento' => 'nullable|numeric|min:0|max:100',
            'items.*.price_list_id' => 'nullable|exists:price_lists,id',
        ]);
    }

    /**
     * Misma matemática que CompraController::store, pero sin tocar stock.
     */
    private function guardarDetalles(PedidoCompra $pedido, array $items): void
    {
        $totalNeto = 0;
        $totalConIva = 0;

        foreach ($items as $item) {
            $precioBase = $item['precio_unitario'];
            $precioFinal = $precioBase;

            if (!empty($item['price_list_id'])) {
                $listItem = PriceListItem::where('price_list_id', $item['price_list_id'])
                    ->where('applicable_id', $item['idarticulo'])
                    ->first();

                if ($listItem) {
                    $precioFinal = $listItem->getEffectivePrice($precioBase);
                }
            }

            $descuento = $item['descuento'] ?? 0;
            $precioConDescuento = $precioFinal - ($precioFinal * $descuento / 100);

            $subtotalNeto = $item['cantidad'] * $precioConDescuento;
            $montoIva = $subtotalNeto * ($item['iva'] / 100);
            $subtotalConIva = $subtotalNeto + $montoIva;

            $pedido->detalles()->create([
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

        $pedido->update([
            'total_neto' => $totalNeto,
            'total_con_iva' => $totalConIva,
        ]);
    }
}
