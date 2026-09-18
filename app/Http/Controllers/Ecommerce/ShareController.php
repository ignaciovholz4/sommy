<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PriceListService;

use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    public static function getByCategory($id)
    {
        $getDataCategory = DB::table('categorias')->where('idcategoria','=', $id)->get();
        return $getDataCategory;
    }

    public static function getAllCategory()
    {
        $getAllCategory = DB::table('categorias')->where('status','=',1)->orderBy('orden')->orderBy('nombre')->get();
        return $getAllCategory;
    }

    public static function getEmpresaImage()
    {
        $empresa = DB::table('configuracion')->first();
        $dataEmpresa = [
            'name'   => $empresa->name ?? 'FacturARG',
            'image'  => $empresa ? "imagenes/empresa/".$empresa->image : 'imagenes/empresa/default.png',
            'adress' => $empresa->adress ?? '',
            'email'  => $empresa->email ?? '',
            'phone'  => $empresa->phone ?? '',
            // Numero para links de WhatsApp en formato internacional (549...):
            // si no esta cargado, cae al telefono comun.
            'whatsapp' => ($empresa->whatsapp ?? '') ?: ($empresa->phone ?? ''),
        ];
        return $dataEmpresa;
    }

    public static function getLimitCategory()
    {
        $getDataCategoryLimit = DB::table('categorias')->where('status', 1)->orderBy('orden')->orderBy('nombre')->take(7)->get();
        return $getDataCategoryLimit;
    }

    /**
     * Traduce el ancho de una medida (ej "0,80 x 1,90" o "1,40x1,90") a como
     * la conoce la gente: 1 plaza, plaza y media, 2 plazas, Queen o King.
     * La medida exacta en cm queda al lado en la tarjeta, esto es solo la
     * etiqueta común para que no haya que saber de memoria qué ancho es qué.
     */
    public static function getPlazaLabel(string $medida): ?string
    {
        $ancho = (float) str_replace(',', '.', trim(explode('x', $medida)[0] ?? ''));
        if ($ancho <= 0) {
            return null;
        }

        return match (true) {
            $ancho < 0.85  => '1 plaza',
            $ancho < 1.30  => '1 plaza y media',
            $ancho < 1.50  => '2 plazas',
            $ancho < 1.80  => 'Queen',
            default        => 'King',
        };
    }

    /**
     * Precio de cada medida de un producto con variantes, para mostrar en la
     * tarjeta en vez de un solo "Desde $X" que obliga a entrar al producto
     * para saber cuánto sale la medida que a cada uno le interesa.
     * Espera $producto con la relación 'combinaciones' ya cargada. El
     * descuento % y la lista de precios (si se pasan) se aplican a cada
     * medida igual que se le aplican al precio "desde", para que la tarjeta
     * no muestre un precio distinto al que después cobra el checkout.
     */
    public static function getVariantesOrdenadas($producto, float $descuentoPct = 0, ?PriceListService $priceListService = null)
    {
        return $producto->combinaciones
            ->where('pventa_variante', '>', 0)
            ->map(function ($v) use ($producto, $descuentoPct, $priceListService) {
                $precio = (float) $v->pventa_variante;
                if ($priceListService) {
                    $precio = $priceListService->getEffectiveSalePrice($producto->idarticulo, $precio);
                }
                if ($descuentoPct > 0) {
                    $precio -= $precio * ($descuentoPct / 100);
                }
                return (object) [
                    'medida' => trim($v->combinacion),
                    'plaza'  => self::getPlazaLabel($v->combinacion),
                    'precio' => $precio,
                ];
            })
            ->sortBy(function ($v) {
                return (float) str_replace(',', '.', trim(explode('x', $v->medida)[0] ?? '0'));
            })
            ->values();
    }

}
