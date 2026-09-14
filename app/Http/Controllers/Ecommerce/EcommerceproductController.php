<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use File;
use Illuminate\Support\Collection;

use App\Http\Controllers\Ecommerce\ShareController;
use App\Http\Controllers\StockController;
use App\Models\Articulo;
use App\Models\PriceListItem;
use App\Models\ProductoImagen;

class EcommerceproductController extends Controller
{
    /**
     * Ruta SEO: /producto/{slug}
     */
    public function showBySlug($slug)
    {
        $articulo = Articulo::where('slug', $slug)->where('estado', 'Activo')->first();

        if (!$articulo) {
            abort(404);
        }

        return $this->show($articulo->idarticulo);
    }

    public function show($id)
    {
        $stockController = new StockController();
        $productoConStock = $stockController->getProductoConStockPorId($id);

        if (!$productoConStock) {
            return redirect('/')->with('error', 'Producto no disponible');
        }

        // (por ahora no)
        // $priceListItem = PriceListItem::where('applicable_id', $id)
        //     ->with('list')
        //     ->first();

        $getProd = [$productoConStock->producto];

        if ($productoConStock->producto->tipo_producto_id === 1) {
            $getVariantesData = null;
            $getEachVarianteProd = null;
            $firstMatchVariant = null;

            // precio base
            $basePrice = $getProd[0]->pventa_con_iva;

            // aplicar descuento
            $hasOffer = false;
            if ($getProd[0]->descuento > 0) {
                $hasOffer = true;
                $basePrice = $basePrice - ($basePrice * ($getProd[0]->descuento / 100));
            }

            // // aplicar lista de precios (por ahora no)
            // $displayPrice = $priceListItem
            //     ? $priceListItem->getEffectiveSalePrice($basePrice)
            //     : $basePrice;

            // (por ahora no)
            // elseif ($priceListItem && $displayPrice < $getProd[0]->pventa_con_iva) {
            //     $hasOffer = true;
            // }

            // agregar campos enriquecidos
            $getProd[0]->display_price = $basePrice;
            $getProd[0]->has_offer = $hasOffer;

            // stock
            $getProd[0]->stock = $productoConStock->total_stock;

        } elseif ($productoConStock->producto->tipo_producto_id === 2) {
            // (por ahora no)
            // if ($priceListItem) {
            //     foreach ($productoConStock->combinaciones as $items) {
            //         $items->combinacion->pventa_variante = $priceListItem->getEffectiveSalePrice(
            //             $items->combinacion->pventa_variante
            //         );
            //     }
            // }
            // Todas las variantes muestran siempre la misma foto del artículo
            // principal (galería de abajo) — no hay foto propia por variante.

            $getVariantesData = $productoConStock->combinaciones->map(fn($c) => $c->combinacion);
            $getEachVarianteProd = $productoConStock->combinaciones;
            $firstMatchVariant = $productoConStock->combinaciones->first();
        } else {
            $getVariantesData = null;
            $getEachVarianteProd = null;
            $firstMatchVariant = null;
        }

        $getCategoryLimit = ShareController::getLimitCategory();
        $arrayEmpresa = ShareController::getEmpresaImage();

        $producto = $productoConStock->producto;

        // Galería del artículo (principal primero); nunca fotos propias de una
        // variante puntual, todas las medidas/colores comparten esta misma galería.
        // Fallback a productos.imagen si no hay filas.
        $imagenesGaleria = ProductoImagen::where('producto_id', $producto->idarticulo)
            ->whereNull('combinacion_id')
            ->orderByDesc('principal')
            ->orderBy('orden')
            ->get();

        // Ficha técnica: solo campos con valor
        $especificaciones = $this->armarEspecificaciones($producto);

        return view('ecommerce.products.index', compact(
            'getProd',
            'getVariantesData',
            'getEachVarianteProd',
            'firstMatchVariant',
            'getCategoryLimit',
            'arrayEmpresa',
            'imagenesGaleria',
            'especificaciones'
        ));
    }

    /**
     * Productos relacionados a recomendar para agregar al carrito, dado un
     * listado de ids de productos que ya están en el carrito (localStorage).
     * GET /Ecommercerelacionados?ids=1,2,3
     */
    public function relacionados(Request $request)
    {
        $idsCarrito = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter()
            ->unique()
            ->values();

        if ($idsCarrito->isEmpty()) {
            return response()->json(['productos' => []]);
        }

        $stockController = new StockController();
        $conStock = $stockController->getProductosConStock()->keyBy('producto_id');

        $idsRelacionados = DB::table('producto_relacionados')
            ->whereIn('idarticulo', $idsCarrito)
            ->pluck('relacionado_id')
            ->unique()
            ->diff($idsCarrito)
            ->values();

        if ($idsRelacionados->isEmpty()) {
            return response()->json(['productos' => []]);
        }

        $articulosRelacionados = Articulo::whereIn('idarticulo', $idsRelacionados)
            ->where('estado', 'Activo')
            ->whereIn('idarticulo', $conStock->keys())
            ->with(['imagenes' => fn ($q) => $q->whereNull('combinacion_id')->orderByDesc('principal')->orderBy('orden')])
            ->limit(8)
            ->get();

        // Variantes con stock de los productos con combinaciones, para poder agregarlos
        // directo desde el widget del carrito (sin pasar por la ficha del producto).
        $idsVariables = $articulosRelacionados->where('tipo_producto_id', 2)->pluck('idarticulo');
        $variantesPorProducto = $idsVariables->isEmpty()
            ? collect()
            : DB::table('sucursal_combinacion')
                ->join('producto_combinaciones', 'producto_combinaciones.idcombinacion', '=', 'sucursal_combinacion.combinacion_id')
                ->where('sucursal_combinacion.activo', 1)
                ->whereIn('producto_combinaciones.producto_id', $idsVariables)
                ->groupBy('producto_combinaciones.idcombinacion', 'producto_combinaciones.producto_id', 'producto_combinaciones.combinacion', 'producto_combinaciones.pventa_variante')
                ->havingRaw('SUM(sucursal_combinacion.stock) > 0')
                ->selectRaw('producto_combinaciones.producto_id, producto_combinaciones.idcombinacion, producto_combinaciones.combinacion as label, producto_combinaciones.pventa_variante as precio, SUM(sucursal_combinacion.stock) as stock')
                ->get()
                ->groupBy('producto_id');

        $productos = $articulosRelacionados
            ->map(function ($p) use ($conStock, $variantesPorProducto) {
                $precioDesde = false;
                $precio = $p->pventa_con_iva;
                $variantes = [];

                if ($p->tipo_producto_id == 2) {
                    $variantes = ($variantesPorProducto->get($p->idarticulo) ?? collect())
                        ->map(fn ($v) => [
                            'idcombinacion' => (int) $v->idcombinacion,
                            'label' => $v->label,
                            'precio' => (float) $v->precio,
                            'stock' => (int) $v->stock,
                        ])
                        ->values();

                    $minVariante = $p->combinaciones()->where('pventa_variante', '>', 0)->min('pventa_variante');
                    if ($minVariante) {
                        $precioDesde = true;
                        $precio = $minVariante;
                    }
                }

                $displayPrice = $precio;
                $hasOffer = false;
                if ($p->descuento > 0) {
                    $hasOffer = true;
                    $displayPrice = $precio - ($precio * ($p->descuento / 100));
                }

                $foto = $p->imagenes->first();

                return [
                    'id' => $p->idarticulo,
                    'nombre' => $p->nombre,
                    'slug' => $p->slug,
                    'precio' => (float) $displayPrice,
                    'precio_desde' => $precioDesde,
                    'has_offer' => $hasOffer,
                    'imagen' => $foto ? asset($foto->path) : ($p->imagen ? asset('imagenes/articulos/' . $p->imagen) : null),
                    'tipo_producto_id' => (int) $p->tipo_producto_id,
                    'stock' => (int) ($conStock->get($p->idarticulo)->total_stock ?? 0),
                    'variantes' => $variantes,
                ];
            })
            ->values();

        return response()->json(['productos' => $productos]);
    }

    /**
     * Tabla de especificaciones del colchón (etiqueta => valor legible), solo campos cargados.
     */
    private function armarEspecificaciones($producto): array
    {
        $specs = [];

        if ($producto->tipo_colchon) {
            $specs['Tipo de colchón'] = Articulo::TIPOS_COLCHON[$producto->tipo_colchon] ?? $producto->tipo_colchon;
        }
        if ($producto->firmeza) {
            $specs['Firmeza'] = Articulo::FIRMEZAS[$producto->firmeza] ?? $producto->firmeza;
        }
        if ($producto->plazas) {
            $specs['Plazas'] = Articulo::PLAZAS[$producto->plazas] ?? $producto->plazas;
        }
        if ($producto->altura_cm) {
            $specs['Altura'] = rtrim(rtrim(number_format($producto->altura_cm, 1, ',', '.'), '0'), ',') . ' cm';
        }
        if ($producto->densidad_kg_m3) {
            $specs['Densidad'] = rtrim(rtrim(number_format($producto->densidad_kg_m3, 2, ',', '.'), '0'), ',') . ' kg/m³';
        }
        if ($producto->peso_max_kg) {
            $specs['Peso máximo por plaza'] = $producto->peso_max_kg . ' kg';
        }
        if (!is_null($producto->pillow_top)) {
            $specs['Pillow top'] = $producto->pillow_top ? 'Sí' : 'No';
        }
        if ($producto->tela) {
            $specs['Tela'] = $producto->tela;
        }

        return $specs;
    }

}