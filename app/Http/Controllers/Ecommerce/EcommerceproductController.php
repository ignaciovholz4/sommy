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

        // Galería (principal primero); fallback a productos.imagen si no hay filas
        $imagenesGaleria = ProductoImagen::where('producto_id', $producto->idarticulo)
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