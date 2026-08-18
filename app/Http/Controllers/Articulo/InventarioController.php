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

        return view('almacen.inventory.index', ['productos' => $productos]);
    }

    /**
     * Export inventory to PDF
     */
    public function store(Request $request)
    {
        $productos = Articulo::where('estado', '=', 'Activo')
            ->get()
            ->map(function ($articulo) {
                return [
                    'Codigo' => $articulo->codigo,
                    'Nombre' => $articulo->nombre,
                    'Descripcion' => $articulo->descripcion,
                    'Precio compra sin IVA' => $articulo->pcompra_sin_iva,
                    'Precio compra con IVA' => $articulo->pcompra_con_iva,
                    'Precio venta sin IVA' => $articulo->pventa_sin_iva,
                    'Precio venta con IVA' => $articulo->pventa_con_iva,
                    'Descuento' => $articulo->descuento,
                    'Tipo producto' => $articulo->tipo_producto_id == 1 ? 'Simple' : 'Personalizado',
                    'Marca' => optional($articulo->marca)->nombre ?? '',
                    'Pesable' => $articulo->articulo_pesable_balanza ? 'Sí' : 'No',
                    'IVA compra' => optional($articulo->ivaCompra)->tipo_iva ?? '',
                    'IVA venta' => optional($articulo->ivaVenta)->tipo_iva ?? '',
                    'Estado' => $articulo->estado,
                ];
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