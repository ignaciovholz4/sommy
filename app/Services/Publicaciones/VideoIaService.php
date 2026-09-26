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

        return 'Video vertical estilo UGC HIPERREALISTA (footage real de celular filmado por otra persona, no selfie, no animación ni render, cero aspecto de video generado por IA): '
            . 'una mujer argentina joven (25-32 años), real y cercana, pelo suelto, ropa casual prolija (campera de cuero o sweater), parada DENTRO de un depósito o local real de colchonería, '
            . 'apoyada o al lado de colchones Sommy reales apilados y algunos todavía envueltos en plástico de fábrica (mantener el colchón de la imagen adjunta fiel a la foto, sin rediseñarlo). '
            . 'Habla a cámara con gestos naturales y suaves de las manos mientras explica, mirando directo al lente, con un tono CÁLIDO, TRANQUILO Y DELICADO — cercana y genuina, nunca apurada ni "vendedora", como quien recomienda algo con cariño a una amiga. '
            . 'ILUMINACIÓN: luz natural pareja y difusa de depósito/local (ventanales o luz de tubo suave), SIN sombras duras ni contraste marcado sobre la cara ni sobre los colchones — nada de un solo foco lateral duro ni sombras teatrales. '
            . 'CÁMARA: la sostiene otra persona (no ella), a la altura de los ojos, con el micro-temblor natural de una mano real, sin selfie stick. '
            . 'RITMO: hablar pausado y con calidez, con silencios naturales y una pausa antes del cierre, usando los 8 segundos completos sin apurar el texto. '
            . 'Incluí SOLAMENTE un subtítulo de texto simple quemado en la parte inferior del video (letras blancas con contorno negro, sin fondo ni globo de dialogo, sin iconos), EXACTAMENTE con este texto, sin inventar ni cambiar palabras: '
            . '"' . $p['nombre'] . ($specs ? '. ' . ucfirst($specs) : '') . '. Directo de fábrica: pagás recién cuando lo recibís.' . $precio . '" '
            . 'Ella dice en voz alta, en español argentino, lo mismo que dice el subtítulo, con calidez genuina y sin sobreactuar, y al final toca el colchón con suavidad, sonriendo con calma. '
            . 'Estética real de contenido de redes: un solo plano continuo, sin cortes. '
            . 'PROHIBIDO agregar cualquier elemento de interfaz de aplicación: nada de nombre de usuario, foto de perfil, iconos de me gusta/comentar/compartir, globos de dialogo, barra de navegacion, ni ningun otro overlay de red social — SOLO el video real y el subtítulo de texto simple indicado arriba, nada más.';
    }

    /**
     * Video de PRESENTACIÓN de producto (sin persona, sin UGC): cinematográfico,
     * tono delicado, foco 100% en el colchón real. Alternativa al video UGC para
     * piezas más institucionales o de catálogo premium.
     */
    public static function promptPresentacion(array $p, bool $conPrecio): string
    {
        $precio = $conPrecio ? ' Precio: $' . number_format($p['precioFinal'], 0, ',', '.') . '.' : '';
        $specs = implode(' · ', array_filter([
            !empty($p['altura']) ? $p['altura'] . ' cm' : null,
            isset($p['firmeza']) && $p['firmeza'] ? 'Firmeza ' . mb_strtolower($p['firmeza']) : null,
            !empty($p['pillow']) ? 'Pillow top' : null,
        ]));

        return 'Video de presentación de producto CINEMATOGRÁFICO e HIPERREALISTA (footage real, no animación ni render 3D, cero aspecto de video generado por IA), SIN NINGUNA PERSONA en cámara. '
            . 'El colchón real de Sommy de la imagen adjunta (mantenerlo EXACTAMENTE fiel a la foto: misma forma, tela, costuras, acolchado y color, sin rediseñarlo) es el único protagonista, apoyado sobre una base o en un estudio fotográfico limpio en tonos Azul Noche y Blanco Quilt, o en un dormitorio real minimalista y luminoso. '
            . 'MOVIMIENTO DE CÁMARA: un solo movimiento lento, suave y continuo (paneo delicado o rotación orbital lenta alrededor del colchón), sin cortes ni movimientos bruscos, transmitiendo calma y calidad premium. '
            . 'ILUMINACIÓN: luz suave, pareja y delicada, sin sombras duras, con un brillo sutil que resalta la textura real del acolchado y las costuras. '
            . 'TONO GENERAL: elegante, sereno y delicado — como un comercial premium de bajo perfil, sin gritar la venta, transmitiendo calidad y confianza a través de la imagen, no de un discurso. '
            . 'SIN VOCES NI DIÁLOGO: no hay ninguna persona hablando ni narrando; es un video puramente visual y ambiental, con sonido ambiente suave apenas perceptible, sin música cantada. '
            . 'Incluí SOLAMENTE un texto elegante y simple quemado en pantalla, apareciendo con un fundido suave (nunca de golpe), letras finas blancas, EXACTAMENTE con este texto, sin inventar ni cambiar palabras: '
            . '"' . mb_strtoupper($p['nombre']) . ($specs ? ' — ' . $specs : '') . '.' . $precio . '" '
            . 'PROHIBIDO agregar cualquier elemento de interfaz de aplicación: nada de nombre de usuario, foto de perfil, iconos, globos de dialogo, barra de navegacion, ni ningun overlay de red social — SOLO el video real y el texto simple indicado arriba, nada más.';
    }

    /**
     * @return array{path: string, url: string, prompt: string}
     */
    public function generarVideo(string $rutaFotoProducto, string $prompt, string $formato, int $duracionSegundos = 8): array
    {
        if (!is_file($rutaFotoProducto)) {
            throw new \RuntimeException('No se encontro la imagen del producto: ' . basename($rutaFotoProducto));
        }

        set_time_limit(400);

        $model = config('services.gemini.video_model', 'veo-3.1-fast-generate-preview');
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
                    'durationSeconds' => $duracionSegundos,
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
