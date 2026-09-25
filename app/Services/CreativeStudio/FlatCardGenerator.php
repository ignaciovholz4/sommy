<?php

namespace App\Services\CreativeStudio;

/**
 * Piezas de marca sin producto ni foto: fondo de color sólido (paleta
 * institucional de Sommy) + texto grande, dibujado con GD directamente en el
 * servidor (sin pasar por Gemini). Da variedad real al feed y evita el riesgo
 * de pedirle a un modelo de imagen que "invente" una escena de marca o que
 * escriba mal el texto — acá el texto siempre es exacto porque lo dibuja código.
 */
class FlatCardGenerator
{
    protected const FORMATOS = [
        'feed'  => [1080, 1350],
        'story' => [1080, 1920],
        'ml'    => [1200, 1200],
    ];

    /** Paleta institucional (nunca la promocional amarillo/fucsia, esa es solo para ofertas puntuales). */
    protected const PALETA = [
        ['fondo' => '#1B2B5A', 'texto' => '#FFFFFF'], // Azul Noche
        ['fondo' => '#F8FAFC', 'texto' => '#1B2B5A'], // Blanco Quilt
        ['fondo' => '#E0F2FE', 'texto' => '#1B2B5A'], // Celeste Claro institucional
    ];

    public function generar(string $texto, string $formato = 'feed', ?int $paletaIndex = null): array
    {
        [$w, $h] = self::FORMATOS[$formato] ?? self::FORMATOS['feed'];
        $colores = self::PALETA[($paletaIndex ?? random_int(0, count(self::PALETA) - 1)) % count(self::PALETA)];

        $im = imagecreatetruecolor($w, $h);
        imagefill($im, 0, 0, $this->hexToColor($im, $colores['fondo']));
        $fg = $this->hexToColor($im, $colores['texto']);

        $font = storage_path('fonts/TitanOne-Regular.ttf');
        $this->dibujarTextoCentrado($im, mb_strtoupper($texto), $fg, $font, $w, $h);
        $this->dibujarFirma($im, $fg, $font, $w, $h);

        $dir = public_path('imagenes/publicaciones/escenas');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $nombre = 'card-' . uniqid('', true) . '.png';
        imagepng($im, $dir . DIRECTORY_SEPARATOR . $nombre);
        imagedestroy($im);

        $relativo = 'imagenes/publicaciones/escenas/' . $nombre;

        return ['path' => $relativo, 'url' => asset($relativo), 'prompt' => '[Tarjeta de marca sin IA: fondo ' . $colores['fondo'] . ' + texto] ' . $texto];
    }

    protected function dibujarTextoCentrado($im, string $texto, int $color, string $font, int $w, int $h): void
    {
        $maxWidth = $w * 0.8;
        $size = $w * 0.09;
        $lineas = [$texto];

        while ($size > 20) {
            $lineas = $this->envolver($texto, $font, $size, $maxWidth);
            if (count($lineas) <= 5) {
                break;
            }
            $size -= 4;
        }

        $lineHeight = $size * 1.4;
        $totalHeight = count($lineas) * $lineHeight;
        $y = ($h - $totalHeight) / 2 + $size;

        foreach ($lineas as $linea) {
            $bbox = imagettfbbox($size, 0, $font, $linea);
            $lineWidth = abs($bbox[4] - $bbox[0]);
            imagettftext($im, $size, 0, (int) (($w - $lineWidth) / 2), (int) $y, $color, $font, $linea);
            $y += $lineHeight;
        }
    }

    protected function dibujarFirma($im, int $color, string $font, int $w, int $h): void
    {
        $size = $w * 0.026;
        $texto = 'SOMMY';
        $bbox = imagettfbbox($size, 0, $font, $texto);
        $lineWidth = abs($bbox[4] - $bbox[0]);
        imagettftext($im, $size, 0, (int) (($w - $lineWidth) / 2), (int) ($h - $h * 0.055), $color, $font, $texto);
    }

    protected function envolver(string $texto, string $font, float $size, float $maxWidth): array
    {
        $palabras = explode(' ', $texto);
        $lineas = [];
        $actual = '';

        foreach ($palabras as $palabra) {
            $prueba = trim($actual . ' ' . $palabra);
            $bbox = imagettfbbox($size, 0, $font, $prueba);
            $anchoPrueba = abs($bbox[4] - $bbox[0]);
            if ($anchoPrueba > $maxWidth && $actual !== '') {
                $lineas[] = $actual;
                $actual = $palabra;
            } else {
                $actual = $prueba;
            }
        }
        if ($actual !== '') {
            $lineas[] = $actual;
        }

        return $lineas;
    }

    protected function hexToColor($im, string $hex): int
    {
        $hex = ltrim($hex, '#');
        return imagecolorallocate($im, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }
}
