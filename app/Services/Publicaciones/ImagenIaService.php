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

    public function generarEscena(string $rutaFotoProducto, string $escena, string $formato, string $instrucciones = '', ?string $promptLibre = null): array
    {
        if (!is_file($rutaFotoProducto)) {
            throw new \RuntimeException('No se encontro la imagen del producto: ' . basename($rutaFotoProducto));
        }

        $orientacion = $formato === 'story'
            ? 'Encuadre vertical 9:16 (historia de Instagram), con aire libre arriba y abajo para superponer textos.'
            : 'Encuadre cuadrado 1:1, con aire en el tercio superior e inferior para superponer textos.';

        // Estructura fija de marca: [producto fiel] + [cuerpo editable] + [encuadre] + [prohibiciones]
        $cuerpo = trim((string) $promptLibre) !== ''
            ? trim($promptLibre)
            : self::cuerpoPrompt($escena, \Illuminate\Support\Facades\DB::table('publicaciones_ajustes')->value('estilo_imagen'))
                . (trim($instrucciones) !== '' ? ' Indicacion extra: ' . trim($instrucciones) : '');

        $prompt = 'Foto publicitaria profesional: colocar este colchon (mantener EXACTAMENTE su forma, tela, costuras, etiqueta y colores reales) sobre una base o sommier en '
            . $cuerpo . ' '
            . $orientacion
            . ' IMPORTANTE: no agregar ningun texto, logo, marca de agua ni precio a la imagen.';

        $model = config('services.gemini.image_model', 'gemini-2.5-flash-image');

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

        $data = base64_decode($imagen['inlineData']['data'] ?? $imagen['inline_data']['data']);

        $dir = public_path('imagenes/publicaciones/escenas');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $nombre = 'escena-' . $escena . '-' . uniqid() . '.png';
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
