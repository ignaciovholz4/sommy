<?php

namespace App\Services\Publicaciones;

use Illuminate\Support\Facades\Http;

/**
 * Genera escenas de producto con Gemini (modelo de imagen): recibe la foto
 * real del colchon y devuelve una fotografia ambientada, manteniendo el
 * producto fiel. El precio/logo NO van en la imagen IA: los superpone el
 * canvas del Estudio con datos exactos del ERP.
 */
class ImagenIaService
{
    /** Presets de escena elegibles desde el Estudio. */
    public const ESCENAS = [
        'dormitorio' => 'un dormitorio real luminoso de estilo escandinavo, luz natural de manana entrando por una ventana, ropa de cama blanca y celeste pastel, madera clara, plantas',
        'noche'      => 'un dormitorio premium de noche, iluminacion calida tenue de veladores, tonos azul profundo y madera oscura, atmosfera serena de hotel boutique',
        'minimal'    => 'un estudio fotografico minimalista con fondo liso en degrade celeste muy suave (#E0F2FE a #F8FAFC), sombra suave debajo del producto, estetica de catalogo premium',
        'familia'    => 'un dormitorio familiar calido y acogedor con luz de tarde, manta tejida, libros en la mesa de luz, sensacion hogarena argentina',
    ];

    /** Se agrega al prompt cuando hay imágenes de referencia (recurso tipo "referencia") adjuntas. */
    protected const INSTRUCCION_REFERENCIAS = 'Ademas de lo anterior, imita el estilo visual general '
        . '(paleta de color, iluminacion, composicion, mood/atmosfera) de las imagenes de referencia adjuntas '
        . 'al final, sin copiar literalmente su contenido ni ningun producto que aparezca en ellas.';

    public function disponible(): bool
    {
        return (bool) config('services.gemini.api_key');
    }

    /**
     * Genera la escena y la guarda en public/imagenes/publicaciones/escenas.
     *
     * @param string $rutaFotoProducto ruta absoluta a la foto real del producto
     * @param string $escena clave de self::ESCENAS
     * @param string $formato ml|post|story (define orientacion pedida)
     * @return array{path: string, url: string, prompt: string}
     */
    /**
     * Cuerpo editable del prompt tal como lo ve el usuario en el Estudio:
     * escena elegida + estilo visual entrenado. La estructura fija (fidelidad
     * del producto, encuadre, prohibiciones) la agrega generarEscena() siempre.
     */
    public static function cuerpoPrompt(string $escena, ?string $estiloEntrenado): string
    {
        $cuerpo = (self::ESCENAS[$escena] ?? self::ESCENAS['dormitorio']) . '.';

        $cuerpo .= trim((string) $estiloEntrenado) !== ''
            ? ' Estilo de la marca: ' . trim($estiloEntrenado)
            : ' Estilo: fotografia comercial realista de alta calidad, colores serenos (azules, celestes, blancos), sin personas.';

        return $cuerpo;
    }

    /** Orientación fija por formato: feed (4:5, default), story (9:16) o ml (1:1). */
    protected function orientacion(string $formato): string
    {
        return match ($formato) {
            'story' => 'Encuadre vertical 9:16 (historia de Instagram/Facebook), con aire libre arriba y abajo para superponer textos.',
            'ml'    => 'Encuadre cuadrado 1:1 (ficha de MercadoLibre), con aire en el tercio superior e inferior para superponer textos.',
            default => 'Encuadre vertical 4:5 (post de feed de Instagram/Facebook), con aire en el tercio superior e inferior para superponer textos.',
        };
    }

    protected function construirPrompt(string $rutaFotoProducto, string $formato, string $instrucciones, ?string $promptLibre, string $escena = 'dormitorio', ?string $extraEscena = null): string
    {
        $cuerpo = trim((string) $promptLibre) !== ''
            ? trim($promptLibre)
            : self::cuerpoPrompt($escena, \Illuminate\Support\Facades\DB::table('publicaciones_ajustes')->value('estilo_imagen'))
                . (trim((string) $extraEscena) !== '' ? ' ' . trim($extraEscena) : '')
                . (trim($instrucciones) !== '' ? ' Indicacion extra: ' . trim($instrucciones) : '');

        return 'Foto publicitaria profesional: colocar este colchon (mantener EXACTAMENTE su forma, tela, costuras, etiqueta y colores reales) sobre una base o sommier en '
            . $cuerpo . ' '
            . $this->orientacion($formato)
            . ' IMPORTANTE: no agregar ningun texto, logo, marca de agua ni precio a la imagen.';
    }

    public function generarEscena(string $rutaFotoProducto, string $escena, string $formato, string $instrucciones = '', ?string $promptLibre = null, ?string $extraEscena = null): array
    {
        if (!is_file($rutaFotoProducto)) {
            throw new \RuntimeException('No se encontro la imagen del producto: ' . basename($rutaFotoProducto));
        }

        $prompt = $this->construirPrompt($rutaFotoProducto, $formato, $instrucciones, $promptLibre, $escena, $extraEscena);
        $data = $this->llamarGemini($prompt, $rutaFotoProducto);

        return $this->guardarImagen($data, $escena, $prompt);
    }

    /**
     * Genera $cantidad variantes de la MISMA escena de marca (para elegir),
     * en paralelo. Cada llamada usa exactamente el mismo prompt fijo: la
     * variedad la da el propio muestreo de Gemini, no el texto.
     *
     * @return array<int, array{path:string,url:string,prompt:string}|array{error:string}>
     */
    public function generarVariantes(string $rutaFotoProducto, string $formato, int $cantidad = 5, string $instrucciones = '', ?string $extraEscena = null): array
    {
        if (!is_file($rutaFotoProducto)) {
            throw new \RuntimeException('No se encontro la imagen del producto: ' . basename($rutaFotoProducto));
        }

        $escena = 'dormitorio'; // único ambiente base: homogeneidad de feed
        $refParts = $this->referenciasParts();
        $prompt = $this->construirPrompt($rutaFotoProducto, $formato, $instrucciones, null, $escena, $extraEscena)
            . ($refParts ? ' ' . self::INSTRUCCION_REFERENCIAS : '');
        $model = config('services.gemini.image_model', 'gemini-3.1-flash-lite-image');
        $mime = $this->mime($rutaFotoProducto);
        $b64 = base64_encode(file_get_contents($rutaFotoProducto));

        $respuestas = Http::pool(fn ($pool) => collect(range(1, max(1, $cantidad)))
            ->map(fn () => $pool->withHeaders(['x-goog-api-key' => config('services.gemini.api_key')])
                ->timeout(120)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [[
                        'parts' => array_merge([
                            ['text' => $prompt],
                            ['inline_data' => ['mime_type' => $mime, 'data' => $b64]],
                        ], $refParts),
                    ]],
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
                $resultados[] = $this->guardarImagen($data, $escena, $prompt);
            } catch (\Throwable $e) {
                $resultados[] = ['error' => $e->getMessage()];
            }
        }

        if (!array_filter($resultados, fn ($r) => !isset($r['error']))) {
            throw new \RuntimeException($resultados[0]['error'] ?? 'Gemini no devolvio ninguna imagen.');
        }

        return $resultados;
    }

    /**
     * Contenido de marca SIN producto puntual (ej. estilo de vida, una persona
     * despertando descansada, un tip de descanso ilustrado). No hay foto real
     * que respetar, así que Gemini genera 100% desde texto — igual sigue el
     * estilo de marca fijo (Mi marca) para mantener el feed homogéneo.
     *
     * @return array<int, array{path:string,url:string,prompt:string}|array{error:string}>
     */
    public function generarVariantesMarca(string $formato, int $cantidad, string $instrucciones): array
    {
        $estilo = \Illuminate\Support\Facades\DB::table('publicaciones_ajustes')->value('estilo_imagen');
        $cuerpo = trim((string) $estilo) !== ''
            ? 'Estilo de la marca: ' . trim($estilo)
            : 'Estilo: fotografia comercial realista de alta calidad, colores serenos (azules, celestes, blancos).';

        $refParts = $this->referenciasParts();

        $prompt = 'Foto de contenido de marca para redes sociales de Sommy (fabrica argentina de colchones), '
            . 'SIN mostrar ningun producto puntual ni logo dentro de la escena. '
            . trim($instrucciones) . '. ' . $cuerpo . ' '
            . $this->orientacion($formato)
            . ' Fotografia realista, personas reales de aspecto argentino si corresponde, nada de texto ni marca de agua en la imagen.'
            . ($refParts ? ' ' . self::INSTRUCCION_REFERENCIAS : '');

        $model = config('services.gemini.image_model', 'gemini-3.1-flash-lite-image');

        $respuestas = Http::pool(fn ($pool) => collect(range(1, max(1, $cantidad)))
            ->map(fn () => $pool->withHeaders(['x-goog-api-key' => config('services.gemini.api_key')])
                ->timeout(120)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [['parts' => array_merge([['text' => $prompt]], $refParts)]],
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
                $resultados[] = $this->guardarImagen($data, 'marca', $prompt);
            } catch (\Throwable $e) {
                $resultados[] = ['error' => $e->getMessage()];
            }
        }

        if (!array_filter($resultados, fn ($r) => !isset($r['error']))) {
            throw new \RuntimeException($resultados[0]['error'] ?? 'Gemini no devolvio ninguna imagen.');
        }

        return $resultados;
    }

    protected function llamarGemini(string $prompt, string $rutaFotoProducto): string
    {
        $model = config('services.gemini.image_model', 'gemini-3.1-flash-lite-image');

        $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.api_key')])
            ->timeout(120)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt],
                        ['inline_data' => [
                            'mime_type' => $this->mime($rutaFotoProducto),
                            'data' => base64_encode(file_get_contents($rutaFotoProducto)),
                        ]],
                    ],
                ]],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $imagen = collect($response->json('candidates.0.content.parts', []))
            ->first(fn ($p) => isset($p['inlineData']['data']) || isset($p['inline_data']['data']));

        if (!$imagen) {
            throw new \RuntimeException('Gemini no devolvio imagen (posible bloqueo de contenido). Proba con otra foto o escena.');
        }

        return base64_decode($imagen['inlineData']['data'] ?? $imagen['inline_data']['data']);
    }

    /** Imágenes de referencia activas (recurso tipo "referencia"), como inline_data listas para Gemini. */
    protected function referenciasParts(int $max = 2): array
    {
        $refs = \Illuminate\Support\Facades\DB::table('publicaciones_recursos')
            ->where('tipo', 'referencia')->where('activo', 1)
            ->whereNotNull('archivo')
            ->orderByDesc('id')->limit($max)->get(['archivo']);

        $parts = [];
        foreach ($refs as $r) {
            $ruta = public_path($r->archivo);
            if (is_file($ruta)) {
                $parts[] = ['inline_data' => ['mime_type' => $this->mime($ruta), 'data' => base64_encode(file_get_contents($ruta))]];
            }
        }

        return $parts;
    }

    protected function guardarImagen(string $data, string $escena, string $prompt): array
    {
        $dir = public_path('imagenes/publicaciones/escenas');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $nombre = 'escena-' . $escena . '-' . uniqid('', true) . '.png';
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
