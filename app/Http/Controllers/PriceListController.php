<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Articulo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PriceListController extends Controller
{
    public function index()
    {
        $salesLists = PriceList::sales()->orderByDesc('id')->get();
        $purchaseLists = PriceList::purchase()->orderByDesc('id')->get();
        return view('almacen.pricelists.index', compact('salesLists', 'purchaseLists'));
    }

    public function create()
    {
        return view('almacen.pricelists.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:amount,product_percentage,general_percentage',
            'context' => 'required|in:sales,purchase',
            'value_type' => 'nullable|in:discount,increase',
            'percentage' => 'nullable|numeric|min:0',
            'applicable' => 'required|in:categoria,marca,articulo',
            'applicable_ids' => 'nullable|array',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Crear la lista
        $list = PriceList::create(
            $request->only([
                'name','type','context','value_type','percentage','description','applicable'
            ]) + ['active' => true]
        );

        // Guardar ítems directamente según applicable
        if ($request->filled('applicable_ids')) {
            foreach ($request->applicable_ids as $id) {
                PriceListItem::create([
                    'price_list_id' => $list->id,
                    'applicable_id' => $id,
                    'applicable_type' => $list->applicable,
                    'percentage' => $list->percentage ?? 0,
                    'purchase_percentage' => $list->percentage ?? 0,
                    'value_type' => $list->value_type,
                    'purchase_value_type' => $list->value_type,
                ]);
            }
        }

        return redirect()->route('pricelists.edit', $list->id)->with('success', 'Lista de precios creada con ítems.');
    }

    public function edit(int $id)
    {
        $list = PriceList::findOrFail($id);
        return view('almacen.pricelists.edit', compact('list'));
    }

    public function update(Request $request, int $id)
    {
        $list = PriceList::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:amount,product_percentage,general_percentage',
            'context' => 'required|in:sales,purchase',
            'value_type' => 'nullable|in:discount,increase',
            'percentage' => 'nullable|numeric|min:0',
            'active' => 'sometimes|boolean',
            'applicable' => 'required|in:categoria,marca,articulo',
            'applicable_ids' => 'nullable|array',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Blindar: no permitir cambiar 'applicable' ni 'type'
        if ($request->applicable !== $list->applicable) {
            return back()->withErrors(['applicable' => 'El campo "Aplicable" no puede modificarse en edición.'])->withInput();
        }

        if ($request->type !== $list->type) {
            return back()->withErrors(['type' => 'El campo "Tipo" no puede modificarse en edición.'])->withInput();
        }

        // Actualizar la lista (sin tocar applicable ni type)
        $list->update(
            $request->only([
                'name','context','value_type','percentage','description','active'
            ])
        );

        // Sincronizar ítems
        $productos = collect($request->applicable_ids ?? []);

        $currentItems = $list->items()->pluck('applicable_id');

        // Eliminar los que ya no están seleccionados
        $toDelete = $currentItems->diff($productos);
        if ($toDelete->isNotEmpty()) {
            PriceListItem::where('price_list_id', $list->id)
                ->whereIn('applicable_id', $toDelete)
                ->delete();
        }

        // Insertar los nuevos
        $toInsert = $productos->diff($currentItems);
        foreach ($toInsert as $id) {
            PriceListItem::create([
                'price_list_id' => $list->id,
                'applicable_id' => $id,
                'applicable_type' => $list->applicable,
                'percentage' => $list->percentage ?? 0,
                'purchase_percentage' => $list->percentage ?? 0,
                'value_type' => $list->value_type,
                'purchase_value_type' => $list->value_type,
            ]);
        }

        return back()->with('success', 'Lista de precios actualizada con ítems.');
    }

    public function destroy(int $id)
    {
        PriceList::findOrFail($id)->delete();
        return redirect()->route('pricelists.index')->with('success', 'Lista de precios eliminada.');
    }

    // API endpoints
    public function getLists()
    {
        $lists = PriceList::where([['active', 1], ['type', 'general_percentage']])
            ->select('id', 'name', 'context', 'value_type', 'percentage')
            ->orderBy('name')
            ->get();
        return response()->json($lists);
    }

    public function getSalesLists()
    {
        $lists = PriceList::sales()->active()
            ->select('id', 'name', 'type', 'value_type', 'percentage')
            ->orderBy('name')
            ->get();
        return response()->json($lists);
    }

    public function getPurchaseLists()
    {
        $lists = PriceList::purchase()->active()
            ->select('id', 'name', 'type', 'value_type', 'percentage')
            ->orderBy('name')
            ->get();
        return response()->json($lists);
    }

    public function getListItems(int $id)
    {
        $list = PriceList::findOrFail($id);

        $items = $list->items()->get()->map(function($item) {
            switch ($item->applicable_type) {
                case 'categoria':
                    $categoria = \App\Models\Categoria::find($item->applicable_id);
                    return [
                        'applicable_id' => $item->applicable_id,
                        'nombre' => $categoria?->nombre,
                        'descripcion' => $categoria?->descripcion,
                        'applicable_type' => 'categoria',
                    ];
                case 'marca':
                    $marca = \App\Models\Marca::find($item->applicable_id);
                    return [
                        'applicable_id' => $item->applicable_id,
                        'nombre' => $marca?->nombre,
                        'descripcion' => $marca?->descripcion,
                        'applicable_type' => 'marca',
                    ];
                case 'articulo':
                    $articulo = \App\Models\Articulo::find($item->applicable_id);
                    return [
                        'applicable_id' => $item->applicable_id,
                        'nombre' => $articulo?->nombre,
                        'codigo' => $articulo?->codigo,
                        'pventa_con_iva' => $articulo?->pventa_con_iva,
                        'applicable_type' => 'articulo',
                    ];
                default:
                    return [
                        'applicable_id' => $item->applicable_id,
                        'applicable_type' => $item->applicable_type,
                    ];
            }
        });

        return response()->json(['items' => $items]);
    }

    public function bulkAttachProducts(Request $request)
    {
        $rules = [
            'price_list_id' => 'required|exists:price_lists,id',
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:productos,idarticulo',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'purchase_percentage' => 'nullable|numeric|min:0|max:100',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $list = PriceList::findOrFail($request->price_list_id);
        $percentage = $request->percentage ?? 0;
        $purchasePercentage = $request->purchase_percentage ?? 0;

        foreach ($request->product_ids as $productId) {
            PriceListItem::firstOrCreate([
                'price_list_id' => $list->id,
                'product_id' => $productId,
            ], [
                'percentage' => $percentage,
                'purchase_percentage' => $purchasePercentage,
                'value_type' => $list->value_type,
                'purchase_value_type' => $list->value_type,
            ]);
        }

        return response()->json(['message' => 'Productos agregados a la lista de precios']);
    }

    public function removeItem(int $itemId)
    {
        $item = PriceListItem::findOrFail($itemId);
        $item->delete();
        return response()->json(['message' => 'Producto removido de la lista']);
    }

    public function loadOptions(string $type)
    {
        switch ($type) {
            case 'categoria':
                $options = \App\Models\Categoria::select('idcategoria as id','nombre','descripcion')->orderBy('nombre')->get();
                break;
            case 'marca':
                $options = \App\Models\Marca::select('idmarca as id','nombre','descripcion')->orderBy('nombre')->get();
                break;
            case 'articulo':
                $options = \App\Models\Articulo::select('idarticulo as id','nombre','codigo','pventa_con_iva')->orderBy('nombre')->get();
                break;
            default:
                $options = collect();
        }
        return response()->json($options);
    }

    public function applicableToArticulo(Request $request, $idarticulo)
    {
        $context = $request->get('context', 'sales'); // por defecto ventas
        $articulo = Articulo::with(['categoria','marca'])->findOrFail($idarticulo);

        $categoriaId = $articulo->categoria_id;
        $marcaId = $articulo->marca_id;

        $lists = PriceList::where('context', $context)->active()->get();

        $filtered = $lists->filter(function($list) use ($idarticulo, $categoriaId, $marcaId) {
            return $list->items()->where(function($q) use ($idarticulo, $categoriaId, $marcaId) {
                $q->where(function($q2) use ($idarticulo) {
                    $q2->where('applicable_type', 'articulo')
                    ->where('applicable_id', $idarticulo);
                })
                ->orWhere(function($q2) use ($categoriaId) {
                    if ($categoriaId) {
                        $q2->where('applicable_type', 'categoria')
                        ->where('applicable_id', $categoriaId);
                    }
                })
                ->orWhere(function($q2) use ($marcaId) {
                    if ($marcaId) {
                        $q2->where('applicable_type', 'marca')
                        ->where('applicable_id', $marcaId);
                    }
                });
            })->exists();
        });

        return response()->json($filtered->values());
    }

}

