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
            $prod->variantes = collect();
            if ($producto->tipo_producto_id == 2) {
                $minVariante = $producto->combinaciones->where('pventa_variante', '>', 0)->min('pventa_variante');
                if ($minVariante) {
                    $prod->precio_desde = true;
                    $prod->variantes = ShareController::getVariantesOrdenadas($producto, (float) $producto->descuento);
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
    public function combosDisponibles()
    {
        return \App\Models\Articulo::where('estado', 'Activo')
            ->where('tipo_producto_id', 2)
            ->where('combo_descuento_pct', '>', 0)
            ->with('combinaciones')
            ->get()
            ->map(function ($anchor) {
                $variantesAncla = $anchor->combinaciones->where('pventa_variante', '>', 0);
                if ($variantesAncla->isEmpty()) {
                    return null;
                }

                // Relacionados con descuento (hoy: la base). Los regalos van aparte:
                // si un producto está configurado como regalo de este ancla, se saca
                // del pool con descuento aunque también esté cargado como relacionado
                // (evita contarlo dos veces si alguien lo deja tildado en los dos lados).
                $regaloIdsAncla = DB::table('producto_regalos')->where('idarticulo', $anchor->idarticulo)->pluck('regalo_id');
                $relacionadosIds = DB::table('producto_relacionados')
                    ->where('idarticulo', $anchor->idarticulo)
                    ->whereNotIn('relacionado_id', $regaloIdsAncla)
                    ->pluck('relacionado_id');
                $relacionados = \App\Models\Articulo::whereIn('idarticulo', $relacionadosIds)
                    ->where('estado', 'Activo')
                    ->with('combinaciones')
                    ->get();

                // Regalos reales (producto_regalos): van a $0 en el combo, pero
                // suman a precio de lista en "precio_separado" para mostrar el
                // ahorro real de llevarlos gratis. Es el mismo para toda medida.
                $regalosValor = 0.0;
                $regalosNombres = [];
                $regalos = DB::table('producto_regalos as pr')
                    ->join('productos as p', 'p.idarticulo', '=', 'pr.regalo_id')
                    ->where('pr.idarticulo', $anchor->idarticulo)
                    ->where('p.estado', 'Activo')
                    ->select('p.nombre', 'p.pventa_con_iva', 'pr.cantidad')
                    ->get();

                foreach ($regalos as $r) {
                    $cant = max(1, (int) $r->cantidad);
                    $regalosValor += (float) $r->pventa_con_iva * $cant;
                    $nombreLimpio = $this->nombreParaMostrar($r->nombre);
                    $regalosNombres[] = $cant > 1 ? "{$nombreLimpio} x{$cant}" : $nombreLimpio;
                }

                $descuento = (float) $anchor->combo_descuento_pct / 100;

                // El combo cambia de precio según la medida del colchón ancla: para
                // cada una buscamos la base/relacionados de esa misma medida y
                // calculamos el total, así se puede mostrar el precio por medida
                // (igual que ya se hace con los productos con variantes).
                $variantesCombo = collect();
                $incluyeMasCompleto = [];
                $incluyeSommierGlobal = false;

                foreach ($variantesAncla as $variante) {
                    $anchorMedida = str_replace(',', '.', trim(explode('x', $variante->combinacion)[0] ?? ''));
                    $anchorPrice = (float) $variante->pventa_variante;
                    $addonsFull = 0.0;
                    $incluye = [];
                    $incluyeSommier = false;

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
                        $nombreLimpio = $this->nombreParaMostrar($rel->nombre);
                        $incluye[] = $nombreLimpio;
                        if (stripos($nombreLimpio, 'sommier') !== false || stripos($nombreLimpio, 'base') !== false) {
                            $incluyeSommier = true;
                        }
                    }

                    if ($addonsFull <= 0 && $regalosValor <= 0) {
                        continue;
                    }

                    $separado = \App\Support\Precio::redondear($anchorPrice + $addonsFull + $regalosValor);
                    $comboTotal = \App\Support\Precio::redondear($anchorPrice + $addonsFull * (1 - $descuento));

                    $variantesCombo->push((object) [
                        'medida'          => trim($variante->combinacion),
                        'plaza'           => ShareController::getPlazaLabel($variante->combinacion),
                        'precio'          => $comboTotal,
                        'precio_separado' => $separado,
                        'ahorro'          => $separado - $comboTotal,
                    ]);

                    // Nos quedamos con el detalle de "incluye" de la medida que más
                    // artículos relacionados sumó, para mostrar en la card (suele ser
                    // la misma lista para todas las medidas, pero por las dudas).
                    if (count($incluye) > count($incluyeMasCompleto)) {
                        $incluyeMasCompleto = $incluye;
                        $incluyeSommierGlobal = $incluyeSommier;
                    }
                }

                if ($variantesCombo->isEmpty()) {
                    return null;
                }

                $variantesCombo = $variantesCombo->sortBy('precio')->values();

                // Medida "de referencia" para el precio principal de la card:
                // 2 plazas si está disponible, si no la más barata.
                $refEntry = $variantesCombo->firstWhere('medida', '1,40 x 1,90') ?? $variantesCombo->first();

                return (object) [
                    'producto'         => $anchor,
                    'medida'           => $refEntry->medida,
                    'plaza'            => $refEntry->plaza,
                    'incluye'          => $incluyeMasCompleto,
                    'incluye_sommier'  => $incluyeSommierGlobal,
                    'regalos'          => $regalosNombres,
                    'display_price'    => $refEntry->precio,
                    'precio_separado'  => $refEntry->precio_separado,
                    'ahorro'           => $refEntry->ahorro,
                    'variantes'        => $variantesCombo,
                    'precio_desde'     => $variantesCombo->count() > 1,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Nombre de producto listo para mostrarle al cliente: saca aclaraciones
     * internas entre paréntesis (ej "Base Sommier Ecocuero (Solo con colchón)"
     * -> "Base Sommier Ecocuero") que sirven para distinguir variantes en el
     * panel pero no aportan nada a un comprador.
     */
    private function nombreParaMostrar(string $nombre): string
    {
        return trim(preg_replace('/\s*\([^)]*\)\s*/', ' ', $nombre));
    }
}