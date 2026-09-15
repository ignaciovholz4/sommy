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

        // "Productos destacados": no son los últimos cargados, son los que se
        // tildan a mano desde el panel (Artículos > editar > "Destacado").
        $getDataProd = $getDataProd->filter(fn ($p) => (bool) $p->producto->destacado)->values();

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

        $getDataCombos = $this->combosDisponibles();

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

    /**
     * Página dedicada con todos los combos en oferta (link desde el menú
     * Categorías). Misma vidriera que la home, en una página propia.
     */
    public function combos()
    {
        $getDataCombos = $this->combosDisponibles();
        $getCategoryLimit = ShareController::getLimitCategory();
        $arrayEmpresa = ShareController::getEmpresaImage();

        return view('ecommerce.combos.index', compact('getDataCombos', 'getCategoryLimit', 'arrayEmpresa'));
    }

    /**
     * Combos en oferta: NO son productos aparte, es una vidriera de los
     * combos dinámicos reales (colchón con combo_descuento_pct + sus
     * relacionados con descuento — hoy la base — más los regalos reales de
     * producto_regalos, que van a $0 con su cantidad, no descontados). El
     * "ahorrás $X" compara contra comprar cada cosa suelta al precio de
     * lista, regalos incluidos a precio de lista.
     */
    private function combosDisponibles()
    {
        return \App\Models\Articulo::where('estado', 'Activo')
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

                // Relacionados con descuento (hoy: la base). Los regalos van aparte.
                $relacionadosIds = DB::table('producto_relacionados')->where('idarticulo', $anchor->idarticulo)->pluck('relacionado_id');
                $relacionados = \App\Models\Articulo::whereIn('idarticulo', $relacionadosIds)
                    ->where('estado', 'Activo')
                    ->with('combinaciones')
                    ->get();

                $descuento = (float) $anchor->combo_descuento_pct / 100;
                $anchorPrice = (float) $variante->pventa_variante;
                // Medida del colchón elegido (ej. "1,40 x 1,90" -> "1.40"), para
                // buscar la variante de la misma medida en los relacionados con
                // variantes (la base), en vez de quedarnos con la más barata.
                $anchorMedida = str_replace(',', '.', trim(explode('x', $variante->combinacion)[0] ?? ''));
                $addonsFull = 0.0;
                $incluye = [];

                foreach ($relacionados as $rel) {
                    if ($rel->tipo_producto_id == 2) {
                        $variantesConPrecio = $rel->combinaciones->where('pventa_variante', '>', 0);
                        $match = $variantesConPrecio->first(fn ($v) => str_replace(',', '.', trim($v->combinacion)) === $anchorMedida);
                        $precioUnit = (float) ($match->pventa_variante ?? $variantesConPrecio->min('pventa_variante') ?? 0);
                    } else {
                        $precioUnit = (float) $rel->pventa_con_iva;
                    }

                    if ($precioUnit <= 0) {
                        continue;
                    }
                    $addonsFull += $precioUnit;
                    $incluye[] = $rel->nombre;
                }

                // Regalos reales (producto_regalos): van a $0 en el combo, pero
                // suman a precio de lista en "precio_separado" para mostrar el
                // ahorro real de llevarlos gratis.
                $regalosValor = 0.0;
                $regalos = DB::table('producto_regalos as pr')
                    ->join('productos as p', 'p.idarticulo', '=', 'pr.regalo_id')
                    ->where('pr.idarticulo', $anchor->idarticulo)
                    ->where('p.estado', 'Activo')
                    ->select('p.nombre', 'p.pventa_con_iva', 'pr.cantidad')
                    ->get();

                foreach ($regalos as $r) {
                    $cant = max(1, (int) $r->cantidad);
                    $regalosValor += (float) $r->pventa_con_iva * $cant;
                    $incluye[] = ($cant > 1 ? "{$r->nombre} x{$cant}" : $r->nombre) . ' de regalo';
                }

                if ($addonsFull <= 0 && $regalosValor <= 0) {
                    return null;
                }

                $separado = $anchorPrice + $addonsFull + $regalosValor;
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
    }
}