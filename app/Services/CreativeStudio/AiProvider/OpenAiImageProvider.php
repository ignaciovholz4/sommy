<?php

namespace App\Services\CreativeStudio\AiProvider;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Implementación OpenAI (gpt-image-1) de ImageProviderInterface — alternativa a
 * Gemini. Usa el endpoint "/v1/images/edits" (no "/generations") porque acepta
 * imagen(es) de entrada como referencia real: es lo que permite la fidelidad de
 * producto (mismo mecanismo que Gemini con inline_data), cosa que dall-e-3 no
 * soporta (es solo texto-a-imagen).
 */
class OpenAiImageProvider implements ImageProviderInterface
{
    public function disponible(): bool
    {
        return (bool) config('services.openai.api_key');
    }

    public function generateScene(string $prompt, array $rutasProducto, array $rutasReferencia, array $rutasLogo, string $aspectRatio, int $cantidad): array
    {
        foreach ($rutasProducto as $ruta) {
            if (!is_file($ruta)) {
                throw new \RuntimeException('No se encontro la imagen del producto: ' . basename($ruta));
            }
        }

        $todasLasImagenes = array_merge($rutasProducto, $rutasReferencia, $rutasLogo);
        if (empty($todasLasImagenes)) {
            // El endpoint de edicion exige al menos una imagen de entrada; si no hay
            // ninguna (pieza de marca sin producto), generamos desde cero con /generations.
            return $this->generarSinImagenes($prompt, $aspectRatio, $cantidad);
        }

        $model = config('services.openai.image_model', 'gpt-image-1');
        $size = $this->size($aspectRatio);
        $apiKey = config('services.openai.api_key');

        $respuestas = Http::pool(fn ($pool) => collect(range(1, max(1, $cantidad)))
            ->map(function () use ($pool, $apiKey, $model, $prompt, $todasLasImagenes, $size) {
                $req = $pool->withToken($apiKey)->timeout(120)->asMultipart();
                foreach ($todasLasImagenes as $i => $ruta) {
                    $req = $req->attach('image[]', file_get_contents($ruta), 'referencia-' . $i . '.' . pathinfo($ruta, PATHINFO_EXTENSION));
                }

                return $req->post('https://api.openai.com/v1/images/edits', [
                    'model' => $model,
                    'prompt' => $prompt,
                    'size' => $size,
                    'input_fidelity' => 'high',
                    'n' => 1,
                ]);
            })
            ->all());

        return $this->procesarRespuestas($respuestas, $prompt);
    }

    /** Genera sin ninguna imagen de referencia (pieza de marca 100% desde texto). */
    protected function generarSinImagenes(string $prompt, string $aspectRatio, int $cantidad): array
    {
        $model = config('services.openai.image_model', 'gpt-image-1');
        $size = $this->size($aspectRatio);
        $apiKey = config('services.openai.api_key');

        $respuestas = Http::pool(fn ($pool) => collect(range(1, max(1, $cantidad)))
            ->map(fn () => $pool->withToken($apiKey)->timeout(120)->post('https://api.openai.com/v1/images/generations', [
                'model' => $model,
                'prompt' => $prompt,
                'size' => $size,
                'n' => 1,
            ]))
            ->all());

        return $this->procesarRespuestas($respuestas, $prompt);
    }

    protected function procesarRespuestas(array $respuestas, string $prompt): array
    {
        $resultados = [];
        foreach ($respuestas as $response) {
            try {
                if ($response instanceof \Throwable) {
                    throw $response;
                }
                if ($response->failed()) {
                    throw new \RuntimeException($response->json('error.message') ?? $response->body());
                }
                $b64 = $response->json('data.0.b64_json');
                if (!$b64) {
                    throw new \RuntimeException('OpenAI no devolvio imagen (posible bloqueo de contenido).');
                }
                $resultados[] = $this->guardarImagen(base64_decode($b64), $prompt);
            } catch (\Throwable $e) {
                $resultados[] = ['error' => $e->getMessage()];
            }
        }

        if (!array_filter($resultados, fn ($r) => !isset($r['error']))) {
            throw new \RuntimeException($resultados[0]['error'] ?? 'OpenAI no devolvio ninguna imagen.');
        }

        return $resultados;
    }

    /** gpt-image-1 solo acepta 1024x1024, 1024x1536 (vertical) o 1536x1024 (horizontal). */
    protected function size(string $aspectRatio): string
    {
        return match ($aspectRatio) {
            'ml' => '1024x1024',
            default => '1024x1536', // feed (4:5) y story (9:16): la vertical disponible mas cercana
        };
    }

    protected function guardarImagen(string $data, string $prompt): array
    {
        $dir = public_path('imagenes/publicaciones/escenas');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $nombre = 'escena-' . uniqid('', true) . '.png';
        file_put_contents($dir . DIRECTORY_SEPARATOR . $nombre, $data);

        $relativo = 'imagenes/publicaciones/escenas/' . $nombre;

        return ['path' => $relativo, 'url' => asset($relativo), 'prompt' => $prompt];
    }
}
