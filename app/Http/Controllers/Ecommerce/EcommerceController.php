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

        // Combos en oferta: NO son productos aparte, es una vidriera de los
        // combos dinámicos reales (colchón con combo_descuento_pct + sus
        // relacionados, con el stock y precio real de cada variante — el
        // mismo armador de la ficha de producto). El "ahorrás $X" compara
        // contra comprar cada cosa suelta al precio de lista. Las almohadas
        // se muestran en cantidad 2 (se arman así en el carrito, sosteniendo
        // el mismo precio con descuento por unidad).
        $getDataCombos = \App\Models\Articulo::where('estado', 'Activo')
            ->where('tipo_producto_id', 2)
            ->where('combo_descuento_pct', '>', 0)
            ->with('combinaciones')
            ->get()
            ->map(function ($anchor) {
                $variante = $anchor->combinaciones->firstWhere('combinacion', '1,40 x 1,90')
                    ?? $anchor->combinaciones->where('pventa_variante', '>', 0)->sortByDesc('pventa_variante')->first();
                if (!$variante) {
                    return null;
                }

                $relacionadosIds = DB::table('producto_relacionados')->where('idarticulo', $anchor->idarticulo)->pluck('relacionado_id');
                $relacionados = \App\Models\Articulo::whereIn('idarticulo', $relacionadosIds)
                    ->where('estado', 'Activo')
                    ->with('combinaciones')
                    ->get();

                $descuento = (float) $anchor->combo_descuento_pct / 100;
                $anchorPrice = (float) $variante->pventa_variante;
                $addonsFull = 0.0;
                $incluye = [];

                foreach ($relacionados as $rel) {
                    $cantidad = stripos($rel->nombre, 'almohada') !== false ? 2 : 1;
                    $precioUnit = $rel->tipo_producto_id == 2
                        ? (float) ($rel->combinaciones->where('pventa_variante', '>', 0)->min('pventa_variante') ?? 0)
                        : (float) $rel->pventa_con_iva;
                    if ($precioUnit <= 0) {
                        continue;
                    }
                    $addonsFull += $precioUnit * $cantidad;
                    $incluye[] = $cantidad > 1 ? "{$rel->nombre} x{$cantidad}" : $rel->nombre;
                }

                if ($addonsFull <= 0) {
                    return null;
                }

                $separado = $anchorPrice + $addonsFull;
                $comboTotal = round($anchorPrice + $addonsFull * (1 - $descuento), 2);

                return (object) [
                    'producto'        => $anchor,
                    'incluye'         => $incluye,
                    'display_price'   => $comboTotal,
                    'precio_separado' => $separado,
                    'ahorro'          => round($separado - $comboTotal, 2),
                ];
            })
            ->filter()
            ->values();

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