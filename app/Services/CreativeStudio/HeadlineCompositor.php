<?php

namespace App\Services\CreativeStudio;

/**
 * Dibuja un titular de texto (banner tipo sticker) directo con GD sobre una
 * imagen ya generada — en vez de pedirle a Gemini que lo escriba (probado
 * repetidas veces que no es confiable: llegó a devolver texto en inglés sin
 * sentido). El texto acá SIEMPRE sale exactamente como se pide, sin errores.
 */
class HeadlineCompositor
{
    /** Pega un banner con el texto dado, centrado horizontalmente, en la franja inferior de la imagen. */
    public function pegarBannerInferior($im, int $w, int $h, string $texto, string $colorFondo = '#1B2B5A'): void
    {
        $font = storage_path('fonts/TitanOne-Regular.ttf');
        $texto = mb_strtoupper($texto);
        $maxWidth = $w * 0.86;
        $size = $w * 0.062;
        $lineas = [$texto];

        while ($size > 18) {
            $lineas = $this->envolver($texto, $font, $size, $maxWidth);
            if (count($lineas) <= 2) {
                break;
            }
            $size -= 3;
        }

        $lineHeight = $size * 1.35;
        $padY = $size * 0.6;
        $bannerH = count($lineas) * $lineHeight + $padY * 2;
        $bannerY = $h - $bannerH - ($h * 0.04);

        [$r, $g, $b] = $this->hexToRgb($colorFondo);
        $fondo = imagecolorallocatealpha($im, $r, $g, $b, 15);
        imagealphablending($im, true);
        imagefilledrectangle($im, 0, (int) $bannerY, $w, (int) ($bannerY + $bannerH), $fondo);

        $blanco = imagecolorallocate($im, 255, 255, 255);
        $y = $bannerY + $padY + $size * 0.78;
        foreach ($lineas as $linea) {
            $bbox = imagettfbbox($size, 0, $font, $linea);
            $lineWidth = abs($bbox[4] - $bbox[0]);
            imagettftext($im, $size, 0, (int) (($w - $lineWidth) / 2), (int) $y, $blanco, $font, $linea);
            $y += $lineHeight;
        }
    }

    protected function envolver(string $texto, string $font, float $size, float $maxWidth): array
    {
        $palabras = explode(' ', $texto);
        $lineas = [];
        $actual = '';
        foreach ($palabras as $palabra) {
            $prueba = trim($actual . ' ' . $palabra);
            $bbox = imagettfbbox($size, 0, $font, $prueba);
            if (abs($bbox[4] - $bbox[0]) > $maxWidth && $actual !== '') {
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

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
