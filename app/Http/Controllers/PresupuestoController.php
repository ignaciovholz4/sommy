<?php

namespace App\Http\Controllers;

use App\Models\Presupuesto;
use App\Models\PresupuestoDetalle;
use App\Models\Cliente;
use App\Models\Articulo;
use App\Models\Venta;
use App\Models\Iva;
use App\Models\CajaApertura;
use App\Models\Movimiento;
use App\Models\Sucursal;
use App\Models\PriceListItem;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PresupuestoController extends Controller
{
    /**
     * Listado de presupuestos
     */
    public function index()
    {
        $presupuestos = Presupuesto::with(['cliente', 'detalles.articulo'])->get();
        return view('presupuestos.index', compact('presupuestos'));
    }

    /**
     * Formulario de creación de presupuesto
     */
    public function create()
    {
        $clientes = Cliente::where('estatus', 'Activo')->orderBy('nombre')->get();
        $articulos = collect();
        $combinaciones = collect();
        $ivas = Iva::all();
        $sucursales = Sucursal::all();

        return view('presupuestos.create', compact(
            'clientes',
            'articulos',
            'combinaciones',
            'ivas',
            'sucursales'
        ));
    }

    /**
     * Guardar nuevo presupuesto
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,idcliente',
            'fecha' => 'required|date',
            'sucursal_id' => 'required|exists:sucursales,id',
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

        DB::beginTransaction();
        try {
            // Crear el presupuesto
            $presupuesto = new Presupuesto();
            $presupuesto->idcliente = $request->cliente_id;
            $presupuesto->fecha = $request->fecha;
            $presupuesto->sucursal_id = $request->sucursal_id;
            $presupuesto->estado = 'borrador';
            $presupuesto->total_neto = 0;
            $presupuesto->total_con_iva = 0;
            $presupuesto->save();

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
                        $precioFinal = $listItem->getEffectiveSalePrice($precioBase);
                    }
                }

                // aplicar descuento adicional
                $descuento = $item['descuento'] ?? 0;
                $precioConDescuento = $precioFinal - ($precioFinal * $descuento / 100);

                $subtotalNeto = $item['cantidad'] * $precioConDescuento;
                $montoIva = $subtotalNeto * ($item['iva'] / 100);
                $subtotalConIva = $subtotalNeto + $montoIva;

                $presupuesto->detalles()->create([
                    'idarticulo'       => $item['idarticulo'],
                    'combinacion_id'   => $item['combinacion_id'] ?? null,
                    'tipo_producto_id' => $item['tipo_producto_id'],
                    'cantidad'         => $item['cantidad'],
                    'precio_unitario'  => $precioBase,
                    'price_list_id' => $item['price_list_id'] ?? null,
                    'descuento'        => $item['descuento'] ?? 0,
                    'iva'              => $item['iva'],
                    'subtotal_neto'    => $subtotalNeto,
                    'subtotal_con_iva' => $subtotalConIva,
                ]);

                $totalNeto += $subtotalNeto;
                $totalConIva += $subtotalConIva;
            }

            $presupuesto->update([
                'total_neto' => $totalNeto,
                'total_con_iva' => $totalConIva,
            ]);

            DB::commit();

            return redirect()->route('presupuestos.index')->with('success', 'Presupuesto creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el presupuesto: '.$e->getMessage()]);
        }
    }

    /**
     * Mostrar presupuesto
     */
    public function show(Presupuesto $presupuesto)
    {
        $presupuesto->load(['cliente', 'detalles.articulo']);
        return view('presupuestos.show', compact('presupuesto'));
    }

    /**
     * Formulario de edición
     */
    public function edit($idpresupuesto)
    {
        $presupuesto = Presupuesto::with([
            'cliente',
            'detalles.articulo',
            'detalles.combinacion.producto',
            'detalles.priceList'
        ])->findOrFail($idpresupuesto);

        $clientes = Cliente::where('estatus', 'Activo')->orderBy('nombre')->get();
        $ivas = Iva::all();
        $sucursales = Sucursal::all();

        // sucursal asociada al presupuesto (si existe)
        $sucursal = $presupuesto->sucursal ?? null;

        // filtrar artículos por sucursal
        $articulos = $sucursal
            ? $sucursal->articulos()->with('articulo.ivaVenta')->get()
            : collect();

        // filtrar combinaciones por sucursal
        $combinaciones = $sucursal
            ? $sucursal->combinaciones()->with('combinacion.producto.ivaVenta')->get()
            : collect();

        return view('presupuestos.edit', compact(
            'presupuesto',
            'clientes',
            'articulos',
            'combinaciones',
            'ivas',
            'sucursales'
        ));
    }

    /**
     * Actualizar presupuesto
     */
    public function update(Request $request, Presupuesto $presupuesto)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,idcliente',
            'fecha' => 'required|date',
            'sucursal_id' => 'required|exists:sucursales,id',
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

        DB::beginTransaction();
        try {
            // Actualizar datos principales
            $presupuesto->update([
                'idcliente' => $request->cliente_id,
                'fecha' => $request->fecha,
                'sucursal_id' => $request->sucursal_id,
                'estado' => $request->estado ?? $presupuesto->estado,
                'total_neto' => 0,
                'total_con_iva' => 0,
            ]);

            // Eliminar detalles anteriores
            $presupuesto->detalles()->delete();

            $totalNeto = 0;
            $totalConIva = 0;

            // Insertar nuevos detalles y acumular totales
            foreach ($request->items as $detalle) {
                $precioBase = $detalle['precio_unitario'];
                $precioFinal = $precioBase;

                //  Ajustar según lista de precios
                if (!empty($detalle['price_list_id'])) {
                    $listItem = PriceListItem::where('price_list_id', $detalle['price_list_id'])
                        ->where('applicable_id', $detalle['idarticulo'])
                        ->first();

                    if ($listItem) {
                        $precioFinal = $listItem->getEffectiveSalePrice($precioBase);
                    }
                }

                $descuento = $detalle['descuento'] ?? 0;
                $precioConDescuento = $precioFinal - ($precioFinal * $descuento / 100);

                $subtotalNeto = $detalle['cantidad'] * $precioConDescuento;
                $montoIva = $subtotalNeto * ($detalle['iva'] / 100);
                $subtotalConIva = $subtotalNeto + $montoIva;

                $presupuesto->detalles()->create([
                    'idarticulo'       => $detalle['idarticulo'],
                    'combinacion_id'   => $detalle['combinacion_id'] ?? null,
                    'tipo_producto_id' => $detalle['tipo_producto_id'],
                    'cantidad'         => $detalle['cantidad'],
                    'precio_unitario'  => $precioBase,
                    'price_list_id'    => $detalle['price_list_id'] ?? null,
                    'descuento'        => $descuento,
                    'iva'              => $detalle['iva'],
                    'subtotal_neto'    => $subtotalNeto,
                    'subtotal_con_iva' => $subtotalConIva,
                ]);

                $totalNeto += $subtotalNeto;
                $totalConIva += $subtotalConIva;
            }

            // Actualizar totales del presupuesto
            $presupuesto->update([
                'total_neto' => $totalNeto,
                'total_con_iva' => $totalConIva,
            ]);

            DB::commit();

            return redirect()->route('presupuestos.index')->with('success', 'Presupuesto actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el presupuesto: '.$e->getMessage()]);
        }
    }

    /**
     * Eliminar presupuesto
     */
    public function destroy(Presupuesto $presupuesto)
    {
        // Eliminar detalles asociados
        $presupuesto->detalles()->delete();

        // Eliminar presupuesto
        $presupuesto->delete();

        return redirect()
            ->route('presupuestos.index')
            ->with('success', 'Presupuesto eliminado correctamente.');
    }

    // Listado para DataTable
    public function list()
    {
        $presupuestos = Presupuesto::with('cliente')->select('presupuestos.*')->get();

        return datatables()->of($presupuestos)
            ->addColumn('cliente', fn($p) => $p->cliente->nombre . ' ' . $p->cliente->paterno)
            ->addColumn('telefono', fn($p) => $p->cliente->telefono)
            ->addColumn('folio', fn($p) => $p->num_folio)
            ->addColumn('total_neto', fn($p) => number_format($p->total_neto, 2, ',', '.'))
            ->addColumn('total_con_iva', fn($p) => number_format($p->total_con_iva, 2, ',', '.'))
            ->addColumn('estado', function($p) {
                $html = '<select class="form-select form-select-sm estado-select" data-id="'.$p->idpresupuesto.'">';

                if ($p->estado === 'borrador') {
                    $html .= '<option value="borrador" selected>Borrador</option>';
                    $html .= '<option value="confirmado">Confirmado</option>';
                } elseif ($p->estado === 'confirmado') {
                    $html .= '<option value="confirmado" selected>Confirmado</option>';
                    $html .= '<option value="venta">Venta</option>';
                } else {
                    // estado venta → ya no se puede cambiar
                    $html .= '<option value="venta" selected>Venta</option>';
                }

                $html .= '</select>';
                return $html;
            })
            ->addColumn('action', function($p) {
                return '
                    <button class="btn btn-sm btn-info" onclick="getDetailQuote('.$p->idpresupuesto.')" title="Ver detalle">
                        <i class="fa fa-eye"></i>
                    </button>
                    <a href="'.route('presupuestos.edit', $p->idpresupuesto).'" class="btn btn-sm btn-warning" title="Editar">
                        <i class="fa fa-edit"></i>
                    </a>
                    <form action="'.route('presupuestos.destroy', $p->idpresupuesto).'" method="POST" style="display:inline-block;">
                        '.csrf_field().method_field('DELETE').'
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Seguro que deseas eliminar este presupuesto?\')" title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                    <a href="'.route('presupuestos.pdf', $p->idpresupuesto).'" target="_blank" class="btn btn-sm btn-secondary" title="Exportar PDF">
                        <i class="fa fa-file-pdf"></i>
                    </a>
                ';
            })
            ->rawColumns(['estado','action'])
            ->make(true);
    }

    // Detalle de un presupuesto
    public function detail($id)
    {
        $presupuesto = Presupuesto::with([
            'cliente',
            'detalles.articulo.ivaVenta',
            'detalles.combinacion',
            'detalles.priceList',
            'sucursal'
        ])->findOrFail($id);

        $detalles = $presupuesto->detalles->map(function($d) {
            return [
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
            ];
        });

        return response()->json([
            'presupuesto' => [
                'cliente' => $presupuesto->cliente->nombre.' '.$presupuesto->cliente->paterno.' '.$presupuesto->cliente->materno,
                'fecha' => \Carbon\Carbon::parse($presupuesto->fecha)->format('d/m/Y'),
                'folio' => $presupuesto->num_folio,
                'sucursal' => $presupuesto->sucursal ? $presupuesto->sucursal->nombre : 'Sin sucursal',
                'sucursal_id' => $presupuesto->sucursal_id,
                'total_neto' => number_format($presupuesto->total_neto, 2, ',', '.'),
                'total_con_iva' => number_format($presupuesto->total_con_iva, 2, ',', '.'),
                'iva_discriminado' => collect($presupuesto->iva_discriminado)->map(function($monto, $porcentaje) {
                    return [
                        'porcentaje' => $porcentaje,
                        'monto' => number_format($monto, 2, ',', '.')
                    ];
                })->values()
            ],
            'detalles' => $detalles,
        ]);
    }

    public function generatePdf($idpresupuesto)
    {
        $presupuesto = Presupuesto::with(['cliente', 'detalles.articulo'])
            ->findOrFail($idpresupuesto);

        // Armamos los detalles
        $detalle = $presupuesto->detalles->map(function($d) {
            return [
                'codigo' => $d->articulo->codigo ?? '',
                'nombre' => $d->articulo->nombre,
                'cantidad' => $d->cantidad,
                'precio_unitario' => number_format($d->precio_unitario, 2, ',', '.'),
                'subtotal_neto' => number_format($d->subtotal_neto, 2, ',', '.'),
                'subtotal_con_iva' => number_format($d->subtotal_con_iva, 2, ',', '.'),
            ];
        });

        // Armamos datos del presupuesto
        $data = [
            'cliente' => $presupuesto->cliente->nombre.' '.$presupuesto->cliente->paterno.' '.$presupuesto->cliente->materno,
            'direccion' => $presupuesto->cliente->direccion ?? '',
            'telefono' => $presupuesto->cliente->telefono ?? '',
            'email' => $presupuesto->cliente->email ?? '',
            'folio' => $presupuesto->num_folio,
            'fecha' => \Carbon\Carbon::parse($presupuesto->fecha)->format('d-m-Y'),
            'estado' => $presupuesto->estado,
            'total_neto' => number_format($presupuesto->total_neto, 2, ',', '.'),
            'total_con_iva' => number_format($presupuesto->total_con_iva, 2, ',', '.'),
            'iva_discriminado' => collect($presupuesto->iva_discriminado)->map(function($monto, $porcentaje) {
                return [
                    'porcentaje' => $porcentaje,
                    'monto' => number_format($monto, 2, ',', '.')
                ];
            }),
            'detalle' => $detalle,
        ];

        $pdf = \PDF::loadView('presupuestos/pdfpresupuesto', $data);
        return $pdf->stream('presupuesto_'.$presupuesto->idpresupuesto.'.pdf');
    }

    public function changeState(Request $request, $id)
    {
        $presupuesto = Presupuesto::with('detalles.articulo', 'cliente')->findOrFail($id);
        $nuevoEstado = $request->input('estado');

        if ($presupuesto->estado === 'borrador' && $nuevoEstado === 'confirmado') {
            $presupuesto->estado = 'confirmado';
            $presupuesto->save();
            return response()->json(['success' => true, 'estado' => 'confirmado']);
        }

        if ($presupuesto->estado === 'confirmado' && $nuevoEstado === 'venta') {
            DB::beginTransaction();
            try {
                // 🔹 Crear venta
                $venta = new Venta();
                $venta->user_id = auth()->id();
                $venta->cliente_id = $presupuesto->idcliente;
                $venta->fecha = $presupuesto->fecha;
                $venta->estado = 'a cobrar'; // estado inicial de la venta
                $venta->tipo_comprobante_id = 3; // Tipo comprobante C
                $venta->sucursal_id = $presupuesto->sucursal_id; // importante para stock
                $venta->total_neto = $presupuesto->total_neto;
                $venta->total_con_iva = $presupuesto->total_con_iva;
                $venta->save();

                // Refrescar para obtener num_folio generado por trigger
                $venta->refresh();

                // 🔹 Crear detalles de la venta y descontar stock
                foreach ($presupuesto->detalles as $detalle) {
                    // Descontar stock en la sucursal
                    app(StockController::class)->disminuirStockEnSucursal(
                        $presupuesto->sucursal_id,
                        $detalle->idarticulo,
                        $detalle->cantidad,
                        $detalle->combinacion_id ?? null
                    );

                    $venta->detalles()->create([
                        'articulo_id'      => $detalle->idarticulo,
                        'combinacion_id'   => $detalle->combinacion_id ?? null,
                        'tipo_producto_id' => $detalle->tipo_producto_id ?? 1,
                        'cantidad'         => $detalle->cantidad,
                        'precio_unitario'  => $detalle->precio_unitario,
                        'descuento'        => $detalle->descuento ?? 0,
                        'iva'              => $detalle->iva,
                        'subtotal_neto'    => $detalle->subtotal_neto,
                        'subtotal_con_iva' => $detalle->subtotal_con_iva,
                        'price_list_id'    => $detalle->price_list_id ?? null,
                    ]);
                }

                // 🔹 Eliminar presupuesto original
                $presupuesto->delete();

                DB::commit();
                return response()->json([
                    'success'   => true,
                    'estado'    => 'venta',
                    'venta_id'  => $venta->idventa,
                    'num_folio' => $venta->num_folio
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Error al convertir a venta: '.$e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'Transición inválida'], 400);
    }

}