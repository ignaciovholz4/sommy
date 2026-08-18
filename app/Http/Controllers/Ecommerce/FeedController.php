<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StockController;
use App\Models\Articulo;
use App\Services\PriceListService;

/**
 * Feed de catálogo de productos en formato Google Merchant (RSS 2.0 + namespace g:).
 * Compatible con: Catálogo de Meta (Instagram/Facebook/WhatsApp) y Google Merchant Center.
 * URL: /feed/productos.xml
 */
class FeedController extends Controller
{
    public function productos(PriceListService $priceListService)
    {
        $stockController = new StockController();
        $conStock = $stockController->getProductosConStock()->keyBy('producto_id');

        $productos = Articulo::where('estado', 'Activo')
            ->whereIn('idarticulo', $conStock->keys())
            ->with('marca')
            ->get();

        $items = $productos->map(function ($p) use ($priceListService, $conStock) {
            $base = $priceListService->getEffectiveSalePrice($p->idarticulo, $p->pventa_con_iva);
            $precio = $p->descuento > 0 ? $base - ($base * $p->descuento / 100) : $base;

            $descripcion = trim(strip_tags($p->descripcion ?? ''));
            if ($descripcion === '') {
                $descripcion = $p->nombre;
            }

            return [
                'id'          => $p->idarticulo,
                'title'       => $p->nombre,
                'description' => mb_substr($descripcion, 0, 4900),
                'link'        => url('/producto/' . $p->slug),
                'image'       => asset('imagenes/articulos/' . $p->imagen),
                'price'       => number_format($precio, 2, '.', '') . ' ARS',
                'brand'       => optional($p->marca)->nombre ?? 'Sommy',
                'stock'       => (int) ($conStock->get($p->idarticulo)->total_stock ?? 0),
            ];
        });

        $xml = view('ecommerce.feed.productos', [
            'items'   => $items,
            'empresa' => ShareController::getEmpresaImage(),
        ])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
