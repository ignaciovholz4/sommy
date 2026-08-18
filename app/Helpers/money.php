<?php

 function format_money_global($amount)
    {
        // NumberFormatter requiere la extensión intl; si no está, formateamos a mano
        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter('es_AR', \NumberFormatter::CURRENCY);
            return $formatter->formatCurrency($amount, 'ARS');
        }

        return '$ ' . number_format((float) $amount, 2, ',', '.');
    }

/**
 * Igual que asset(), pero agrega la fecha de modificación del archivo como versión.
 * Así, cuando subimos un CSS/JS nuevo, el navegador y el CDN lo descargan sí o sí
 * (sin esto quedan hasta 7 días mostrando la versión vieja cacheada).
 */
if (!function_exists('assetv')) {
    function assetv(string $path): string
    {
        $ruta = public_path(ltrim($path, '/'));
        $version = is_file($ruta) ? filemtime($ruta) : null;

        return asset($path) . ($version ? '?v=' . $version : '');
    }
}