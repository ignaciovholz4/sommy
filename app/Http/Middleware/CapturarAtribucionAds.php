<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Captura fbclid/UTM de la URL cuando alguien entra desde un anuncio de Meta
 * y los guarda en cookie (30 dias) para poder atribuir la venta si compra
 * mas adelante. Se lee en EcommerceorderController::store(), mismo patron
 * que la cookie de atribucion de revendedores (RevendedorPublicController).
 */
class CapturarAtribucionAds
{
    public const COOKIE = 'sommy_ads_attr';
    public const DIAS = 30;

    public function handle(Request $request, Closure $next)
    {
        if ($request->hasAny(['fbclid', 'utm_source', 'utm_campaign'])) {
            $datos = [
                'fbclid' => $request->query('fbclid'),
                'utm_source' => $request->query('utm_source'),
                'utm_medium' => $request->query('utm_medium'),
                'utm_campaign' => $request->query('utm_campaign'),
            ];

            Cookie::queue(Cookie::make(self::COOKIE, json_encode($datos), 60 * 24 * self::DIAS));
        }

        return $next($request);
    }
}
