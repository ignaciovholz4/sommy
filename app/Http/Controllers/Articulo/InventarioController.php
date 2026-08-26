<?php

namespace App\Http\Controllers\Articulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use PDF;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use App\Models\Articulo;

class InventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('haveaccess','almacen_inventario.index');

        // Usamos el modelo Articulo que apunta a la tabla productos
        $productos = Articulo::where('estado', '=', 'Activo')->get();
        $puedeVerCostos = auth()->user()->havePermission('productos.ver_costos');

        return view('almacen.inventory.index', ['productos' => $productos, 'puedeVerCostos' => $puedeVerCostos]);
    }

    /**
     * Export inventory to PDF
     */
    public function store(Request $request)
    {
        // Stock total por producto: simples (sucursal_articulo) + variantes (sucursal_combinacion)
        // — mismo cálculo que usa la grilla de /almacen/articulo (ArticuloController::show).
        $stockSimples = DB::table('sucursal_articulo')
            ->where('activo', 1)
            ->selectRaw('articulo_id, SUM(stock) as stock')
            ->groupBy('articulo_id');

        $stockVariantes = DB::table('producto_combinaciones as pc')
            ->join('sucursal_combinacion as scb', 'scb.combinacion_id', '=', 'pc.idcombinacion')
            ->where('scb.activo', 1)
            ->selectRaw('pc.producto_id, SUM(scb.stock) as stock')
            ->groupBy('pc.producto_id');

        $stockPorArticulo = DB::table('productos as p')
            ->leftJoinSub($stockSimples, 'ss', 'ss.articulo_id', '=', 'p.idarticulo')
            ->leftJoinSub($stockVariantes, 'sv', 'sv.producto_id', '=', 'p.idarticulo')
            ->selectRaw('p.idarticulo, COALESCE(ss.stock, 0) + COALESCE(sv.stock, 0) as stock_total')
            ->pluck('stock_total', 'idarticulo');

        $puedeVerCostos = auth()->user()->havePermission('productos.ver_costos');

        $productos = Articulo::where('estado', '=', 'Activo')
            ->get()
            ->map(function ($articulo) use ($stockPorArticulo, $puedeVerCostos) {
                $margenPct = $articulo->pcompra_sin_iva > 0
                    ? round((($articulo->pventa_sin_iva - $articulo->pcompra_sin_iva) / $articulo->pcompra_sin_iva) * 100)
                    : null;

                return array_merge([
                    'Codigo' => $articulo->codigo,
                    'Nombre' => $articulo->nombre,
                    'Descripcion' => $articulo->descripcion,
                    'Stock' => (float) ($stockPorArticulo[$articulo->idarticulo] ?? 0),
                ], $puedeVerCostos ? [
                    'Precio compra sin IVA' => $articulo->pcompra_sin_iva,
                    'Margen %' => $margenPct,
                ] : [], [
                    'Precio venta sin IVA' => $articulo->pventa_sin_iva,
                    'Tipo producto' => $articulo->tipo_producto_id == 1 ? 'Simple' : 'Personalizado',
                    'Pesable' => $articulo->articulo_pesable_balanza ? 'Sí' : 'No',
                    'IVA compra' => optional($articulo->ivaCompra)->tipo_iva ?? '',
                    'IVA venta' => optional($articulo->ivaVenta)->tipo_iva ?? '',
                    'Estado' => $articulo->estado,
                ]);
            });

        $pdf = PDF::loadView("almacen.inventory.pdfinventory", ["productos" => $productos]);
        return $pdf->download('inventario_productos.pdf');
    }

    /**
     * Generate barcode labels for products
     */
    public function generateBarcodeProducts(Request $request)
    {
        $product = Articulo::where('estado', '=', 'Activo')
            ->where('idarticulo', '=', $request->productoId)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $productArray = $product->toArray();
        $prodArray = array_fill(0, $request->quantity, $productArray);

        $prodArray = array_map(function($item) {
            $item['barcode'] = DNS1D::getBarcodeHTML($item['codigo'], 'C128');
            return $item;
        }, $prodArray);

        $productsArray = $prodArray;

        $pdf = PDF::loadView("almacen.inventory.pdf-barcode-product", compact('productsArray'));
        $paperWidth = 85;
        $paperHeight = 200;
        $customPaper = [0, 0, $paperWidth * 2.83465, $paperHeight * 2.83465];

        $pdf->setPaper($customPaper);
        $pdf->setOptions([
            'defaultFont' => 'Courier',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->stream('barcode.pdf');
    }
}