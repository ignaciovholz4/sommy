<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PriceListService;
use App\Http\Controllers\Ecommerce\ShareController;
use App\Http\Controllers\StockController;
use App\Models\Articulo;

class EcommercesearchcategoryController extends Controller
{
    protected $priceListService;

    public function __construct(PriceListService $priceListService)
    {
        $this->priceListService = $priceListService;
    }

    /**
     * Búsqueda de productos (global u opcionalmente dentro de una categoría).
     * GET /buscar?q=texto[&categoria=id]
     */
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));
        $categoriaId = $request->input('categoria');

        $stockController = new StockController();
        $conStock = $stockController->getProductosConStock()->keyBy('producto_id');

        $query = Articulo::where('estado', 'Activo')
            ->whereIn('idarticulo', $conStock->keys());

        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$busqueda}%")
                  ->orWhere('codigo', $busqueda);
            });
        }

        if (!empty($categoriaId)) {
            $query->where('categoria_id', $categoriaId);
        }

        $paginado = $query->orderBy('nombre')->paginate(12)->withQueryString();

        $paginado->setCollection(
            $paginado->getCollection()->map(function ($articulo) use ($conStock) {
                // Con variantes el precio real es por medida/color, no el precio
                // base del producto: se muestra "Desde $" con la más barata.
                $precioDesde = false;
                $precioBase = $articulo->pventa_con_iva;
                if ($articulo->tipo_producto_id == 2) {
                    $minVariante = $articulo->combinaciones->where('pventa_variante', '>', 0)->min('pventa_variante');
                    if ($minVariante) {
                        $precioDesde = true;
                        $precioBase = $minVariante;
                    }
                }

                $basePrice = $this->priceListService->getEffectiveSalePrice(
                    $articulo->idarticulo,
                    $precioBase
                );

                $displayPrice = $basePrice;
                $hasOffer = false;

                if ($articulo->descuento > 0) {
                    $hasOffer = true;
                    $displayPrice = $basePrice - ($basePrice * ($articulo->descuento / 100));
                } elseif ($basePrice < $precioBase) {
                    $hasOffer = true;
                }

                $obj = new \stdClass();
                $obj->producto = $articulo;
                $obj->total_stock = $conStock->get($articulo->idarticulo)->total_stock ?? 0;
                $obj->has_offer = $hasOffer;
                $obj->display_price = $displayPrice;
                $obj->precio_desde = $precioDesde;
                $obj->precio_base = $basePrice;

                return $obj;
            })
        );

        $getDataProd = $paginado;
        $getCategoryLimit = ShareController::getLimitCategory();
        $arrayEmpresa = ShareController::getEmpresaImage();

        return view('ecommerce.search.index', compact(
            'getDataProd',
            'busqueda',
            'getCategoryLimit',
            'arrayEmpresa'
        ));
    }
}
