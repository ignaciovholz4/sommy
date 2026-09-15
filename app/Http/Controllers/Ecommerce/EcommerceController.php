<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\DB;
use App\Services\PriceListService;
use App\Http\Controllers\Ecommerce\ShareController;
use App\Models\PriceList;

class EcommerceController extends Controller
{
    protected $priceListService;

    public function __construct(PriceListService $priceListService)
    {
        $this->priceListService = $priceListService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stockController = new StockController();
        $getDataProd = $stockController->getProductosConStock();

        // enriquecemos cada producto con bandera de oferta y precio a mostrar
        $getDataProd->transform(function ($prod) {
            $producto = $prod->producto;

            // Con variantes el precio real es por medida/color, no el precio
            // base del producto (que ni se usa): se muestra "Desde $" con la
            // variante más barata que tenga precio cargado.
            $prod->precio_desde = false;
            if ($producto->tipo_producto_id == 2) {
                $minVariante = $producto->combinaciones->where('pventa_variante', '>', 0)->min('pventa_variante');
                if ($minVariante) {
                    $prod->precio_desde = true;
                }
            }

            // precio base
            $basePrice = $prod->precio_desde ? $minVariante : $producto->pventa_con_iva;

            $displayPrice = $basePrice;
            // precio efectivo con listas
            // $displayPrice = $this->priceListService->getEffectiveSalePrice(
            //     $producto->idarticulo,
            //     $basePrice
            // );

            // bandera de oferta:
            $hasOffer = false;
            if ($producto->descuento > 0) {
                $hasOffer = true;
                $displayPrice = $basePrice - ($basePrice * ($producto->descuento / 100));
            }
            // else {
            //     // ✅ solo marcar oferta si hay listas activas y el precio efectivo es menor
            //     $activeLists = PriceList::where('context', 'sales')
            //         ->where('active', true)
            //         ->exists();

            //     if ($activeLists && $displayPrice < $basePrice) {
            //         $hasOffer = true;
            //     }
            // }

            $prod->has_offer = $hasOffer;
            $prod->display_price = $displayPrice;
            $prod->precio_base = $basePrice;

            return $prod;
        });

        $getDataBanner = DB::table('banner_ecommerce as be')->where('status','=', 1)->orderBy('orden')->orderBy('banner_id')->get();
        $getReels = \App\Models\configuracion\InstagramReel::where('status', 1)->orderBy('orden')->orderBy('id')->get();
        $getDataCategory = ShareController::getAllCategory();
        $getCategoryLimit = ShareController::getLimitCategory();
        $arrayEmpresa = ShareController::getEmpresaImage();

        // Categoría destino del buscador guiado de colchones
        $categoriaFinder = \App\Models\Categoria::where('slug', 'colchones')->first();
        if (!$categoriaFinder) {
            $catFinderId = \App\Models\Articulo::whereNotNull('tipo_colchon')->value('categoria_id');
            $categoriaFinder = $catFinderId ? \App\Models\Categoria::find($catFinderId) : null;
        }

        // Combos en oferta: productos de la categoría "Conjunto Sommier" (colchón +
        // base + almohadas de regalo, todo en un solo precio). El "ahorrás $X" compara
        // contra comprar los mismos componentes por separado al precio de lista actual
        // (colchón 140x190 + base 140 + 2 almohadas), fijo por combo porque son SKUs
        // armados a mano, no un kit dinámico con componentes vinculados en la base.
        $categoriaCombos = \App\Models\Categoria::where('slug', 'conjunto-sommier')->first();
        $getDataCombos = collect();
        if ($categoriaCombos) {
            $precioSeparado = [
                7 => 297270.00, // Conjunto Nube: colchón 224.000 + base 49.270 + 2 almohadas 24.000
                8 => 320270.00, // Conjunto Cielo: colchón 247.000 + base 49.270 + 2 almohadas 24.000
                9 => 368270.00, // Conjunto Eclipse: colchón 295.000 + base 49.270 + 2 almohadas 24.000
            ];
            $getDataCombos = $getDataProd
                ->filter(fn ($p) => $p->producto->categoria_id === $categoriaCombos->idcategoria)
                ->map(function ($p) use ($precioSeparado) {
                    $separado = $precioSeparado[$p->producto->idarticulo] ?? null;
                    $p->precio_separado = $separado;
                    $p->ahorro = $separado ? round($separado - $p->display_price, 2) : null;
                    return $p;
                })
                ->values();

            // Los combos tienen su propia sección más abajo: que no se dupliquen
            // también en "Últimos productos".
            $getDataProd = $getDataProd
                ->reject(fn ($p) => $p->producto->categoria_id === $categoriaCombos->idcategoria)
                ->values();
        }

        return view('welcome', compact(
            'getDataProd',
            'getDataCombos',
            'getDataCategory',
            'getCategoryLimit',
            'arrayEmpresa',
            'getDataBanner',
            'categoriaFinder',
            'getReels'
        ));
    }
}