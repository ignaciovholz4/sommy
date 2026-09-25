<?php

namespace App\Services\CreativeStudio\AiProvider;

use Illuminate\Support\Facades\Http;

/**
 * Implementación Gemini de ImageProviderInterface.
 *
 * Usa el endpoint clásico ":generateContent" (probado en producción con
 * dinero real). Existe también un endpoint más nuevo, "/v1beta/interactions",
 * confirmado funcional (spike del 2026-09-25: devuelve una imagen real en
 * steps[].content[0].data con response_format.aspect_ratio nativo), pero
 * migrar el proveedor activo a un endpoint no probado en producción es un
 * riesgo que no vale la pena todavía — queda documentado acá para adoptarlo
 * el día que se necesite aspect_ratio nativo o edición multi-turno real.
 */
class GeminiImageProvider implements ImageProviderInterface
{
    public function disponible(): bool
    {
        return (bool) config('services.gemini.api_key');
    }

    public function generateScene(string $prompt, ?string $rutaFotoProducto, array $rutasReferencia, string $aspectRatio, int $cantidad): array
    {
        if ($rutaFotoProducto !== null && !is_file($rutaFotoProducto)) {
            throw new \RuntimeException('No se encontro la imagen del producto: ' . basename($rutaFotoProducto));
        }

        $parts = [['text' => $prompt]];
        if ($rutaFotoProducto !== null) {
            $parts[] = ['inline_data' => ['mime_type' => $this->mime($rutaFotoProducto), 'data' => base64_encode(file_get_contents($rutaFotoProducto))]];
        }
        foreach ($rutasReferencia as $ruta) {
            if (is_file($ruta)) {
                $parts[] = ['inline_data' => ['mime_type' => $this->mime($ruta), 'data' => base64_encode(file_get_contents($ruta))]];
            }
        }

        $model = config('services.gemini.image_model', 'gemini-3.1-flash-lite-image');

        $respuestas = Http::pool(fn ($pool) => collect(range(1, max(1, $cantidad)))
            ->map(fn () => $pool->withHeaders(['x-goog-api-key' => config('services.gemini.api_key')])
                ->timeout(120)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [['parts' => $parts]],
                ]))
            ->all());

        $resultados = [];
        foreach ($respuestas as $response) {
            try {
                if ($response instanceof \Throwable) {
                    throw $response;
                }
                if ($response->failed()) {
                    throw new \RuntimeException($response->json('error.message') ?? $response->body());
                }
                $imagen = collect($response->json('candidates.0.content.parts', []))
                    ->first(fn ($p) => isset($p['inlineData']['data']) || isset($p['inline_data']['data']));
                if (!$imagen) {
                    throw new \RuntimeException('Gemini no devolvio imagen (posible bloqueo de contenido).');
                }
                $data = base64_decode($imagen['inlineData']['data'] ?? $imagen['inline_data']['data']);
                $resultados[] = $this->guardarImagen($data, $prompt);
            } catch (\Throwable $e) {
                $resultados[] = ['error' => $e->getMessage()];
            }
        }

        if (!array_filter($resultados, fn ($r) => !isset($r['error']))) {
            throw new \RuntimeException($resultados[0]['error'] ?? 'Gemini no devolvio ninguna imagen.');
        }

        return $resultados;
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

    protected function mime(string $ruta): string
    {
        return match (strtolower(pathinfo($ruta, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }
}
