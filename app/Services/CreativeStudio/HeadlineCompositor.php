<?php

namespace App\Services\CreativeStudio;

/**
 * Dibuja el titular como una cinta/sticker rotada (estilo de las imágenes de
 * referencia de la marca: amarillo/fucsia, letra gruesa blanca con contorno
 * oscuro, banderines en las puntas) directo con GD sobre una imagen ya
 * generada — en vez de pedirle a Gemini que lo escriba. El texto SIEMPRE sale
 * exactamente como se pide, con la estética de sticker que la marca usa.
 */
class HeadlineCompositor
{
    protected const AMARILLO = '#FFE600';
    protected const FUCSIA = '#FF1F8F';
    protected const NAVY = '#1B2B5A';
    protected const NEGRO = '#111111';

    /** Pega una cinta con el texto dado, centrada, en el tercio inferior de la imagen. */
    public function pegarBannerInferior($im, int $w, int $h, string $texto, string $colorCinta = self::AMARILLO): void
    {
        $font = storage_path('fonts/TitanOne-Regular.ttf');
        $texto = mb_strtoupper($texto);

        $maxWidth = $w * 0.74;
        $size = $w * 0.078;
        while ($size > 20 && $this->anchoTexto($texto, $font, $size) > $maxWidth) {
            $size -= 2;
        }

        $anchoTexto = $this->anchoTexto($texto, $font, $size);
        $padX = $size * 1.0;
        $cintaW = $anchoTexto + $padX * 2;
        $cintaH = $size * 1.75;

        $cx = $w / 2;
        $cy = $h - ($h * 0.16);

        $colorAcento = $colorCinta === self::AMARILLO ? self::FUCSIA : self::AMARILLO;
        $this->dibujarCinta($im, $cx, $cy, $cintaW, $cintaH, -4, $colorCinta, $texto, $size, $font, $colorAcento);
    }

    /**
     * Sticker completo estilo campaña: titular (cinta fucsia) + precio grande (cinta amarilla)
     * + specs reales (barra oscura sin rotar) — igual al lenguaje visual de las referencias de marca.
     * $precio y $specs son opcionales: si no hay, solo se dibuja el titular.
     */
    public function pegarStickerCompleto($im, int $w, int $h, string $headline, ?string $precio = null, ?string $specs = null): void
    {
        $font = storage_path('fonts/TitanOne-Regular.ttf');
        $headline = mb_strtoupper($headline);
        $maxWidth = $w * 0.74;

        $sizeHead = $w * 0.072;
        while ($sizeHead > 18 && $this->anchoTexto($headline, $font, $sizeHead) > $maxWidth) {
            $sizeHead -= 2;
        }
        $cintaHeadW = $this->anchoTexto($headline, $font, $sizeHead) + $sizeHead * 2;
        $cintaHeadH = $sizeHead * 1.7;

        $cx = $w / 2;
        $cyHead = $h - ($h * ($precio ? 0.30 : 0.16));
        $this->dibujarCinta($im, $cx, $cyHead, $cintaHeadW, $cintaHeadH, -4, self::AMARILLO, $headline, $sizeHead, $font, self::FUCSIA);

        if ($precio) {
            $sizePrecio = $w * 0.11;
            while ($sizePrecio > 24 && $this->anchoTexto($precio, $font, $sizePrecio) > $maxWidth) {
                $sizePrecio -= 2;
            }
            $cintaPrecioW = $this->anchoTexto($precio, $font, $sizePrecio) + $sizePrecio * 1.4;
            $cintaPrecioH = $sizePrecio * 1.55;
            $cyPrecio = $cyHead + $cintaHeadH * 0.5 + $cintaPrecioH * 0.5;
            $this->dibujarCinta($im, $cx, $cyPrecio, $cintaPrecioW, $cintaPrecioH, -2, self::FUCSIA, $precio, $sizePrecio, $font);

            if ($specs) {
                $sizeSpecs = $w * 0.032;
                $anchoSpecs = $this->anchoTexto(mb_strtoupper($specs), $font, $sizeSpecs);
                [$nr, $ng, $nb] = $this->hexToRgb(self::NEGRO);
                $negro = imagecolorallocate($im, $nr, $ng, $nb);
                $blanco = imagecolorallocate($im, 255, 255, 255);
                $padX = $sizeSpecs * 1.2;
                $barraY = (int) ($cyPrecio + $cintaPrecioH * 0.5);
                imagefilledrectangle($im, (int) ($cx - $anchoSpecs / 2 - $padX), $barraY, (int) ($cx + $anchoSpecs / 2 + $padX), (int) ($barraY + $sizeSpecs * 1.8), $negro);
                imagettftext($im, $sizeSpecs, 0, (int) ($cx - $anchoSpecs / 2), (int) ($barraY + $sizeSpecs * 1.35), $blanco, $font, mb_strtoupper($specs));
            }
        }
    }

    /** Cinta simple (parallelogramo recto, sin puntas de flecha) con contorno negro y texto con contorno — igual a las referencias de marca. */
    protected function dibujarCinta($destino, float $cx, float $cy, float $cintaW, float $cintaH, float $anguloGrados, string $colorHex, string $texto, float $size, string $font, ?string $colorAcento = null): void
    {
        $margen = $cintaH * 1.6;
        $tmpW = (int) ($cintaW + $margen * 2);
        $tmpH = (int) ($cintaH + $margen * 2);

        $tmp = imagecreatetruecolor($tmpW, $tmpH);
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        $transparente = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
        imagefilledrectangle($tmp, 0, 0, $tmpW, $tmpH, $transparente);
        imagealphablending($tmp, true);

        [$r, $g, $b] = $this->hexToRgb($colorHex);
        $fondo = imagecolorallocate($tmp, $r, $g, $b);
        [$nr, $ng, $nb] = $this->hexToRgb(self::NEGRO);
        $negro = imagecolorallocate($tmp, $nr, $ng, $nb);

        $rx0 = $margen;
        $ry0 = $margen;
        $rx1 = $margen + $cintaW;
        $ry1 = $margen + $cintaH;

        // Triangulitos de acento sueltos, apenas separados de la cinta (esquinas opuestas) — no son parte del borde.
        if ($colorAcento) {
            [$ar, $ag, $ab] = $this->hexToRgb($colorAcento);
            $acento = imagecolorallocate($tmp, $ar, $ag, $ab);
            $t = $cintaH * 0.4;
            $gap = $cintaH * 0.12;
            imagefilledpolygon($tmp, [
                $rx0 - $gap, $ry0 + $cintaH * 0.15,
                $rx0 - $gap - $t, $ry0 + $cintaH * 0.5,
                $rx0 - $gap, $ry0 + $cintaH * 0.85,
            ], $acento);
            imagefilledpolygon($tmp, [
                $rx1 + $gap, $ry1 - $cintaH * 0.15,
                $rx1 + $gap + $t, $ry1 - $cintaH * 0.5,
                $rx1 + $gap, $ry1 - $cintaH * 0.85,
            ], $acento);
        }

        $puntos = [$rx0, $ry0, $rx1, $ry0, $rx1, $ry1, $rx0, $ry1];
        imagefilledpolygon($tmp, $puntos, $fondo);
        imagesetthickness($tmp, max(3, (int) ($cintaH * 0.07)));
        imagepolygon($tmp, $puntos, $negro);

        $blanco = imagecolorallocate($tmp, 255, 255, 255);
        $tx = $margen + $cintaW / 2;
        $ty = $margen + $cintaH / 2 + $size * 0.34;
        $this->textoConContorno($tmp, $texto, (int) $tx, (int) $ty, $size, $font, $blanco, $negro, max(2, (int) ($size * 0.08)));

        $rotado = imagerotate($tmp, -$anguloGrados, $transparente);
        imagesavealpha($rotado, true);
        imagedestroy($tmp);

        $rw = imagesx($rotado);
        $rh = imagesy($rotado);
        imagealphablending($destino, true);
        imagecopy($destino, $rotado, (int) ($cx - $rw / 2), (int) ($cy - $rh / 2), 0, 0, $rw, $rh);
        imagedestroy($rotado);
    }

    /** Texto centrado horizontalmente en ($cx, $cy) con contorno grueso (dibuja el trazo desplazado y el relleno encima). */
    protected function textoConContorno($im, string $texto, int $cx, int $cy, float $size, string $font, int $colorTexto, int $colorContorno, int $grosor): void
    {
        $tw = $this->anchoTexto($texto, $font, $size);
        $x = $cx - $tw / 2;

        for ($dx = -$grosor; $dx <= $grosor; $dx++) {
            for ($dy = -$grosor; $dy <= $grosor; $dy++) {
                if ($dx === 0 && $dy === 0) {
                    continue;
                }
                imagettftext($im, $size, 0, (int) ($x + $dx), (int) ($cy + $dy), $colorContorno, $font, $texto);
            }
        }
        imagettftext($im, $size, 0, (int) $x, (int) $cy, $colorTexto, $font, $texto);
    }

    protected function anchoTexto(string $texto, string $font, float $size): float
    {
        $bbox = imagettfbbox($size, 0, $font, $texto);

        return abs($bbox[4] - $bbox[0]);
    }

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
