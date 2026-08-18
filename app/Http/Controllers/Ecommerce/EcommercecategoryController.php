<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PriceListService;
use App\Http\Controllers\Ecommerce\ShareController;
use App\Http\Controllers\StockController;
use App\Models\Articulo;
use App\Models\Categoria;

class EcommercecategoryController extends Controller
{
    protected $priceListService;

    public function __construct(PriceListService $priceListService)
    {
        $this->priceListService = $priceListService;
    }

    /**
     * Ruta SEO: /categoria/{slug}
     */
    public function showBySlug($slug, Request $request)
    {
        $categoria = Categoria::where('slug', $slug)->first();

        if (!$categoria) {
            abort(404);
        }

        return $this->show($categoria->idcategoria, $request);
    }

    /**
     * Catálogo completo: todos los productos activos con stock, de todas las categorías,
     * con filtro por categoría (chips) y ordenamiento.
     */
    public function todos(Request $request)
    {
        $stockController = new StockController();
        $conStock = $stockController->getProductosConStock()->keyBy('producto_id');

        $query = Articulo::where('estado', 'Activo')->whereIn('idarticulo', $conStock->keys());

        $categoriaActiva = null;
        if ($slug = $request->input('categoria')) {
            $cat = Categoria::where('slug', $slug)->first();
            if ($cat) {
                $query->where('categoria_id', $cat->idcategoria);
                $categoriaActiva = $cat->slug;
            }
        }

        switch ($request->input('orden')) {
            case 'precio_asc':
                $query->orderBy('pventa_con_iva', 'asc');
                break;
            case 'precio_desc':
                $query->orderBy('pventa_con_iva', 'desc');
                break;
            case 'nuevos':
                $query->orderBy('idarticulo', 'desc');
                break;
            case 'ofertas':
                $query->orderBy('descuento', 'desc')->orderBy('nombre');
                break;
            default:
                $query->orderBy('nombre');
        }

        $paginado = $query->paginate(16)->withQueryString();

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
        $categorias = ShareController::getAllCategory();
        $getCategoryLimit = ShareController::getLimitCategory();
        $arrayEmpresa = ShareController::getEmpresaImage();

        return view('ecommerce.catalogo.index', compact(
            'getDataProd', 'categorias', 'categoriaActiva', 'getCategoryLimit', 'arrayEmpresa'
        ));
    }

    /**
     * Listado de productos de una categoría, con filtros de colchonería y paginación.
     */
    public function show($id, Request $request = null)
    {
        $request = $request ?? request();

        // Productos con stock (map producto_id => total_stock)
        $stockController = new StockController();
        $conStock = $stockController->getProductosConStock()->keyBy('producto_id');

        $query = Articulo::where('estado', 'Activo')
            ->where('categoria_id', $id)
            ->whereIn('idarticulo', $conStock->keys());

        // Filtros del sidebar (multiselección)
        if ($firmeza = $request->input('firmeza')) {
            $query->whereIn('firmeza', (array) $firmeza);
        }
        if ($tipo = $request->input('tipo')) {
            $query->whereIn('tipo_colchon', (array) $tipo);
        }
        if ($plazas = $request->input('plazas')) {
            $query->whereIn('plazas', (array) $plazas);
        }
        if ($marcas = $request->input('marca')) {
            $query->whereIn('marca_id', (array) $marcas);
        }
        if ($request->boolean('oferta')) {
            $query->where('descuento', '>', 0);
        }
        if ($request->boolean('pillow')) {
            $query->where('pillow_top', 1);
        }
        if ($request->filled('altura_min')) {
            $query->where('altura_cm', '>=', (float) $request->input('altura_min'));
        }
        if ($request->filled('precio_min')) {
            $query->where('pventa_con_iva', '>=', (float) $request->input('precio_min'));
        }
        if ($request->filled('precio_max')) {
            $query->where('pventa_con_iva', '<=', (float) $request->input('precio_max'));
        }

        // Ordenamiento elegido por el visitante
        switch ($request->input('orden')) {
            case 'precio_asc':
                $query->orderBy('pventa_con_iva', 'asc');
                break;
            case 'precio_desc':
                $query->orderBy('pventa_con_iva', 'desc');
                break;
            case 'nuevos':
                $query->orderBy('idarticulo', 'desc');
                break;
            case 'ofertas':
                $query->orderBy('descuento', 'desc')->orderBy('nombre');
                break;
            default:
                $query->orderBy('nombre');
        }

        $paginado = $query->paginate(12)->withQueryString();

        // Enriquecer manteniendo la estructura que espera la vista (->producto, ->total_stock, ->has_offer, ->display_price)
        $paginado->setCollection(
            $paginado->getCollection()->map(function ($articulo) use ($conStock) {
                // Precio efectivo con listas de precios activas
                $basePrice = $this->priceListService->getEffectiveSalePrice(
                    $articulo->idarticulo,
                    $articulo->pventa_con_iva
                );

                $displayPrice = $basePrice;
                $hasOffer = false;

                // Descuento del producto: SIEMPRE porcentual (coherente con home y ficha)
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

        // Valores de filtro disponibles dentro de esta categoría (para no mostrar opciones vacías)
        $opcionesFiltro = [
            'firmezas' => Articulo::where('categoria_id', $id)->whereNotNull('firmeza')->distinct()->pluck('firmeza'),
            'tipos'    => Articulo::where('categoria_id', $id)->whereNotNull('tipo_colchon')->distinct()->pluck('tipo_colchon'),
            'plazas'   => Articulo::where('categoria_id', $id)->whereNotNull('plazas')->distinct()->pluck('plazas'),
            'marcas'   => DB::table('marcas')
                ->join('productos', 'productos.marca_id', '=', 'marcas.idmarca')
                ->where('productos.categoria_id', $id)
                ->where('productos.estado', 'Activo')
                ->distinct()
                ->orderBy('marcas.nombre')
                ->get(['marcas.idmarca', 'marcas.nombre']),
            'hayOfertas'   => Articulo::where('categoria_id', $id)->where('descuento', '>', 0)->exists(),
            'hayPillowTop' => Articulo::where('categoria_id', $id)->where('pillow_top', 1)->exists(),
        ];

        $getDataProd = $paginado; // la vista itera el paginador directamente

        $getCategory = ShareController::getByCategory($id);
        $getCategoryLimit = ShareController::getLimitCategory();
        $arrayEmpresa = ShareController::getEmpresaImage();

        return view('ecommerce.category.index', compact(
            'getDataProd',
            'getCategory',
            'getCategoryLimit',
            'arrayEmpresa',
            'opcionesFiltro'
        ));
    }
}
