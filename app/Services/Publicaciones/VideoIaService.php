<?php

namespace App\Services\Publicaciones;

use Illuminate\Support\Facades\Http;

/**
 * Genera videos publicitarios con IA (Google Veo, misma API key de Gemini):
 * estilo UGC "selfie" — una persona presenta el producto a cámara y lo vende,
 * con audio hablado incluido. Usa la foto real del producto como referencia.
 *
 * La generación es una operación larga (1-3 minutos): se lanza y se sondea
 * hasta que el video está listo.
 */
class VideoIaService
{
    public function disponible(): bool
    {
        return (bool) config('services.gemini.api_key');
    }

    /** Guión/prompt editable que ve el usuario, armado desde la ficha del producto. */
    public static function promptBase(array $p, bool $conPrecio): string
    {
        $precio = $conPrecio ? ' Cuesta $' . number_format($p['precioFinal'], 0, ',', '.') . ($p['descuento'] > 0 ? ' con ' . round($p['descuento']) . '% de descuento' : '') . '.' : '';
        $specs = implode(', ', array_filter([
            $p['plazas'] ?? null,
            isset($p['firmeza']) && $p['firmeza'] ? 'firmeza ' . mb_strtolower($p['firmeza']) : null,
            !empty($p['pillow']) ? 'pillow top' : null,
            isset($p['noches']) && $p['noches'] ? $p['noches'] . ' noches de prueba' : null,
        ]));

        return 'Video selfie vertical estilo UGC: una persona argentina de unos 35 años, real y cercana, '
            . 'se graba a sí misma en primera persona con el celular en su dormitorio luminoso, mostrando el colchón de la imagen '
            . '(mantener el colchón fiel a la foto). Habla a cámara en español argentino, con entusiasmo genuino y sin sobreactuar: '
            . '"Chicos, tengo que mostrarles el ' . $p['nombre'] . '. ' . ($specs ? ucfirst($specs) . '. ' : '')
            . 'Es directo de fábrica, sin intermediarios.' . $precio . ' Se los recomiendo de verdad, duermo increíble." '
            . 'Al final palmea el colchón sonriendo. Estética casera de video para redes: cámara en mano, luz natural, un solo plano.';
    }

    /**
     * @return array{path: string, url: string, prompt: string}
     */
    public function generarVideo(string $rutaFotoProducto, string $prompt, string $formato): array
    {
        if (!is_file($rutaFotoProducto)) {
            throw new \RuntimeException('No se encontro la imagen del producto: ' . basename($rutaFotoProducto));
        }

        set_time_limit(400);

        $model = config('services.gemini.video_model', 'veo-3.0-fast-generate-001');
        $apiKey = config('services.gemini.api_key');
        $base = 'https://generativelanguage.googleapis.com/v1beta';

        $lanzamiento = Http::withHeaders(['x-goog-api-key' => $apiKey])
            ->timeout(60)
            ->post("{$base}/models/{$model}:predictLongRunning", [
                'instances' => [[
                    'prompt' => $prompt,
                    'image' => [
                        'bytesBase64Encoded' => base64_encode(file_get_contents($rutaFotoProducto)),
                        'mimeType' => $this->mime($rutaFotoProducto),
                    ],
                ]],
                'parameters' => [
                    'aspectRatio' => $formato === 'story' ? '9:16' : '16:9',
                ],
            ]);

        if ($lanzamiento->failed()) {
            throw new \RuntimeException('Veo error: ' . ($lanzamiento->json('error.message') ?? $lanzamiento->body()));
        }

        $operacion = $lanzamiento->json('name');
        if (!$operacion) {
            throw new \RuntimeException('Veo no devolvió la operación de video.');
        }

        // Sondeo hasta ~5 minutos
        $intentos = 0;
        do {
            sleep(10);
            $estado = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout(30)
                ->get("{$base}/{$operacion}");

            if ($estado->failed()) {
                throw new \RuntimeException('Veo (estado): ' . ($estado->json('error.message') ?? $estado->body()));
            }
        } while (!$estado->json('done') && ++$intentos < 30);

        if (!$estado->json('done')) {
            throw new \RuntimeException('El video sigue procesándose; probá de nuevo en un minuto.');
        }

        if ($estado->json('error')) {
            throw new \RuntimeException('Veo: ' . ($estado->json('error.message') ?? 'error desconocido'));
        }

        // La URI del video llega con distintas claves según la versión del modelo
        $uri = $estado->json('response.generateVideoResponse.generatedSamples.0.video.uri')
            ?? $estado->json('response.generatedVideos.0.video.uri');

        if (!$uri) {
            throw new \RuntimeException('Veo terminó pero no devolvió video (posible bloqueo de contenido). Ajustá el guión.');
        }

        $video = Http::withHeaders(['x-goog-api-key' => $apiKey])->timeout(120)->get($uri);
        if ($video->failed()) {
            throw new \RuntimeException('No se pudo descargar el video generado.');
        }

        $dir = public_path('imagenes/publicaciones/videos');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $nombre = 'video-' . uniqid() . '.mp4';
        file_put_contents($dir . DIRECTORY_SEPARATOR . $nombre, $video->body());

        $relativo = 'imagenes/publicaciones/videos/' . $nombre;

        return ['path' => $relativo, 'url' => asset($relativo), 'prompt' => $prompt];
    }

    protected function mime(string $ruta): string
    {
        return match (strtolower(pathinfo($ruta, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }
}
