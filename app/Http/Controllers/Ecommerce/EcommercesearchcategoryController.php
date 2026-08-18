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
                $basePrice = $this->priceListService->getEffectiveSalePrice(
                    $articulo->idarticulo,
                    $articulo->pventa_con_iva
                );

                $displayPrice = $basePrice;
                $hasOffer = false;

                if ($articulo->descuento > 0) {
                    $hasOffer = true;
                    $displayPrice = $basePrice - ($basePrice * ($articulo->descuento / 100));
                } elseif ($basePrice < $articulo->pventa_con_iva) {
                    $hasOffer = true;
                }

                $obj = new \stdClass();
                $obj->producto = $articulo;
                $obj->total_stock = $conStock->get($articulo->idarticulo)->total_stock ?? 0;
                $obj->has_offer = $hasOffer;
                $obj->display_price = $displayPrice;

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
