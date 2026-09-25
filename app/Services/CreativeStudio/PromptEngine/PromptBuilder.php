<?php

namespace App\Services\CreativeStudio\PromptEngine;

use Illuminate\Support\Facades\DB;

/**
 * Arma el prompt final que se manda a Gemini, combinando: estilo de marca
 * fijo (Mi marca) + fidelidad de producto + encuadre pedido + indicación
 * puntual del usuario + reglas negativas automáticas. Un solo lugar para
 * esta lógica: lo usan tanto el chat (ImagenIaService) como el modo Create.
 */
class PromptBuilder
{
    /** Presets de escena base. */
    public const ESCENAS = [
        'dormitorio' => 'un dormitorio real luminoso de estilo escandinavo, luz natural de manana entrando por una ventana, ropa de cama blanca y celeste pastel, madera clara, plantas',
        'noche'      => 'un dormitorio premium de noche, iluminacion calida tenue de veladores, tonos azul profundo y madera oscura, atmosfera serena de hotel boutique',
        'minimal'    => 'un estudio fotografico minimalista con fondo liso en degrade celeste muy suave (#E0F2FE a #F8FAFC), sombra suave debajo del producto, estetica de catalogo premium',
        'familia'    => 'un dormitorio familiar calido y acogedor con luz de tarde, manta tejida, libros en la mesa de luz, sensacion hogarena argentina',
    ];

    protected static function estiloMarca(): string
    {
        $estilo = DB::table('publicaciones_ajustes')->value('estilo_imagen');

        return trim((string) $estilo) !== ''
            ? trim($estilo)
            : 'fotografia comercial realista de alta calidad, colores serenos (azules, celestes, blancos), sin personas';
    }

    protected static function orientacion(string $formato): string
    {
        return match ($formato) {
            'story' => 'Encuadre vertical 9:16 (historia de Instagram/Facebook), con aire libre arriba y abajo para superponer textos.',
            'ml'    => 'Encuadre cuadrado 1:1 (ficha de MercadoLibre), con aire en el tercio superior e inferior para superponer textos.',
            default => 'Encuadre vertical 4:5 (post de feed de Instagram/Facebook), con aire en el tercio superior e inferior para superponer textos.',
        };
    }

    /**
     * Prompt para una escena CON producto real (fidelidad obligatoria).
     *
     * @param string|null $extra indicación puntual del usuario o de un preset (escena/cámara/composición/text safe zone)
     * @param bool $conReferencias si hay imágenes de referencia de estilo adjuntas
     */
    public static function paraProducto(string $formato, string $escena = 'dormitorio', ?string $extra = null, bool $conReferencias = false, ?string $promptLibre = null): string
    {
        if (trim((string) $promptLibre) !== '') {
            $cuerpo = trim($promptLibre);
        } else {
            $cuerpo = (self::ESCENAS[$escena] ?? self::ESCENAS['dormitorio']) . '. '
                . 'Estilo de la marca: ' . self::estiloMarca()
                . (trim((string) $extra) !== '' ? ' ' . trim($extra) : '');
        }

        $prompt = 'Foto publicitaria profesional: colocar este colchon (mantener EXACTAMENTE su forma, tela, costuras, etiqueta y colores reales) sobre una base o sommier en '
            . $cuerpo . ' '
            . self::orientacion($formato)
            . ' IMPORTANTE: no agregar ningun texto, logo, marca de agua ni precio a la imagen. '
            . NegativeRulesBuilder::paraProducto();

        $reglasExtra = NegativeRulesBuilder::extra();
        if ($reglasExtra !== '') {
            $prompt .= ' ' . $reglasExtra;
        }

        if ($conReferencias) {
            $prompt .= ' Ademas, imita el estilo visual general (paleta de color, iluminacion, composicion, mood/atmosfera) '
                . 'de las imagenes de referencia adjuntas al final, sin copiar literalmente su contenido ni ningun producto que aparezca en ellas.';
        }

        return $prompt;
    }

    /** Prompt para contenido de marca SIN producto puntual (estilo de vida, informativo). */
    public static function sinProducto(string $formato, string $instrucciones, bool $conReferencias = false): string
    {
        $prompt = 'Foto de contenido de marca para redes sociales de Sommy (fabrica argentina de colchones), '
            . 'SIN mostrar ningun producto puntual ni logo dentro de la escena. '
            . trim($instrucciones) . '. Estilo de la marca: ' . self::estiloMarca() . ' '
            . self::orientacion($formato)
            . ' Fotografia realista, personas reales de aspecto argentino si corresponde, nada de texto ni marca de agua en la imagen.';

        if ($conReferencias) {
            $prompt .= ' Ademas, imita el estilo visual general (paleta de color, iluminacion, composicion, mood/atmosfera) '
                . 'de las imagenes de referencia adjuntas al final, sin copiar literalmente su contenido.';
        }

        return $prompt;
    }
}
