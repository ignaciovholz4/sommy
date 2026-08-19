<?php

namespace App\Services\Envios;

use Illuminate\Support\Facades\Http;

/**
 * Geocodificación (dirección → lat/lng) con Nominatim de OpenStreetMap
 * (gratuito, sin API key) y armado de la ruta más eficiente de reparto
 * desde el depósito, con el algoritmo del vecino más cercano.
 */
class GeocodificadorService
{
    /** Depósito de salida (configurable por .env). */
    public static function deposito(): array
    {
        return [
            'direccion' => env('DEPOSITO_DIRECCION', 'Ambrosio Funes 1961, Córdoba, Argentina'),
            'lat' => (float) env('DEPOSITO_LAT', -31.4317482),
            'lng' => (float) env('DEPOSITO_LNG', -64.1493714),
        ];
    }

    /** Dirección → [lat, lng] o null. Sesgado a Córdoba/Argentina. */
    public function geocodificar(string $direccion): ?array
    {
        $consulta = trim($direccion);
        if ($consulta === '') {
            return null;
        }
        // Si la dirección no menciona provincia/país, se asume Córdoba, Argentina
        if (!preg_match('/c[oó]rdoba|argentina/i', $consulta)) {
            $consulta .= ', Córdoba, Argentina';
        }

        $params = [
            'q' => $consulta,
            'format' => 'json',
            'limit' => 1,
            'countrycodes' => 'ar',
        ];

        foreach ([true, false] as $verificarSsl) {
            try {
                $cliente = Http::withHeaders(['User-Agent' => 'SommyERP/1.0 (logistica)'])->timeout(10);
                if (!$verificarSsl) {
                    // Fallback para entornos locales sin bundle de certificados (Windows):
                    // los datos son públicos, la degradación es aceptable acá.
                    $cliente = $cliente->withoutVerifying();
                }

                $hit = $cliente->get('https://nominatim.openstreetmap.org/search', $params)->json('0');
                if ($hit && isset($hit['lat'], $hit['lon'])) {
                    return ['lat' => (float) $hit['lat'], 'lng' => (float) $hit['lon']];
                }

                return null; // respondió sin resultados: no reintentar
            } catch (\Throwable $e) {
                // error de conexión/SSL: probar el fallback; si ya era el fallback, desistir
            }
        }

        return null;
    }

    /** Distancia haversine en km. */
    public static function distanciaKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Ordena las paradas con vecino más cercano desde el depósito.
     * Las paradas sin coordenadas quedan al final, en su orden original.
     *
     * @param \Illuminate\Support\Collection $envios (con lat/lng nullable)
     * @return array{ordenados: \Illuminate\Support\Collection, km_total: float}
     */
    public function ordenarRuta($envios): array
    {
        $dep = self::deposito();
        $conCoords = $envios->filter(fn ($e) => $e->lat && $e->lng)->values()->all();
        $sinCoords = $envios->filter(fn ($e) => !$e->lat || !$e->lng)->values();

        $ordenados = [];
        $kmTotal = 0.0;
        $lat = $dep['lat'];
        $lng = $dep['lng'];

        while ($conCoords) {
            $mejorIdx = 0;
            $mejorDist = INF;
            foreach ($conCoords as $i => $e) {
                $d = self::distanciaKm($lat, $lng, (float) $e->lat, (float) $e->lng);
                if ($d < $mejorDist) {
                    $mejorDist = $d;
                    $mejorIdx = $i;
                }
            }
            $elegido = array_splice($conCoords, $mejorIdx, 1)[0];
            $elegido->distancia_tramo = round($mejorDist, 1);
            $kmTotal += $mejorDist;
            $lat = (float) $elegido->lat;
            $lng = (float) $elegido->lng;
            $ordenados[] = $elegido;
        }

        return [
            'ordenados' => collect($ordenados)->concat($sinCoords),
            'km_total' => round($kmTotal, 1),
        ];
    }
}
