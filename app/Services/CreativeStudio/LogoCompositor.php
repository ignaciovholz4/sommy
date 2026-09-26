<?php

namespace App\Services\CreativeStudio;

use Illuminate\Support\Facades\DB;

/**
 * Pega el logo REAL de Sommy (archivo subido en Recursos de marca) sobre una
 * imagen ya generada, en vez de dejar que la IA lo dibuje de memoria (texto
 * genérico, tipografía distinta, sin el detalle de la pluma dorada). Elige
 * automáticamente la variante color (fondos claros) o blanco (fondos oscuros).
 */
class LogoCompositor
{
    /**
     * Pegado con fondo transparente real (sin placa), arriba a la izquierda.
     * Detecta automáticamente si esa esquina de la foto es oscura o clara y
     * elige la variante blanca o color del logo para que siempre se vea bien.
     */
    public function pegarConPlaca($im, int $w, int $h): void
    {
        $lw = (int) ($w * 0.30);
        $lx = (int) ($w * 0.055);
        $ly = (int) ($h * 0.045);

        $fondoOscuro = $this->esZonaOscura($im, $lx, $ly, $lw, (int) ($lw * 0.4));

        $logo = $this->cargarLogo($fondoOscuro);
        if (!$logo) {
            return;
        }

        $lh = (int) ($lw * imagesy($logo) / imagesx($logo));
        $this->pegarRedimensionado($im, $logo, $lx, $ly, $lw, $lh);
        imagedestroy($logo);
    }

    /** Promedia el brillo de una zona de la imagen para decidir si el fondo ahí es oscuro. */
    protected function esZonaOscura($im, int $x, int $y, int $w, int $h): bool
    {
        $anchoImg = imagesx($im);
        $altoImg = imagesy($im);
        $paso = 6;
        $total = 0;
        $muestras = 0;

        for ($px = $x; $px < min($x + $w, $anchoImg); $px += $paso) {
            for ($py = $y; $py < min($y + $h, $altoImg); $py += $paso) {
                $color = imagecolorat($im, $px, $py);
                $rgb = imagecolorsforindex($im, $color);
                $total += (0.299 * $rgb['red'] + 0.587 * $rgb['green'] + 0.114 * $rgb['blue']);
                $muestras++;
            }
        }

        if ($muestras === 0) {
            return false;
        }

        return ($total / $muestras) < 140;
    }

    /** Pegado directo (sin placa), eligiendo la variante blanca si el fondo es oscuro. */
    public function pegarDirecto($im, int $w, int $h, bool $fondoOscuro): void
    {
        $logo = $this->cargarLogo($fondoOscuro);
        if (!$logo) {
            return;
        }

        $lw = (int) ($w * 0.34);
        $lh = (int) ($lw * imagesy($logo) / imagesx($logo));
        $lx = (int) (($w - $lw) / 2);
        $ly = (int) ($h * 0.06);

        $this->pegarRedimensionado($im, $logo, $lx, $ly, $lw, $lh);
        imagedestroy($logo);
    }

    /**
     * Redimensiona el logo a un lienzo temporal con canal alfa real (sin blending, para no
     * mezclar los bordes contra negro) y recién ahí lo copia sobre el destino con blending —
     * imagecopyresampled directo pierde la transparencia de PNG/WEBP.
     */
    protected function pegarRedimensionado($destino, $logo, int $x, int $y, int $w, int $h): void
    {
        $tmp = imagecreatetruecolor($w, $h);
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        imagefilledrectangle($tmp, 0, 0, $w, $h, imagecolorallocatealpha($tmp, 0, 0, 0, 127));
        imagecopyresampled($tmp, $logo, 0, 0, 0, 0, $w, $h, imagesx($logo), imagesy($logo));

        imagealphablending($destino, true);
        imagecopy($destino, $tmp, $x, $y, 0, 0, $w, $h);
        imagedestroy($tmp);
    }

    /** @return \GdImage|null recurso GD con canal alfa preservado, o null si no hay logo cargado en Recursos */
    protected function cargarLogo(bool $blanco)
    {
        $recursos = DB::table('publicaciones_recursos')->where('tipo', 'logo')->where('activo', 1)->get(['titulo', 'archivo']);
        if ($recursos->isEmpty()) {
            return null;
        }

        $elegido = $recursos->first(fn ($r) => $blanco === str_contains(mb_strtolower($r->titulo), 'blanco'))
            ?? $recursos->first();

        $ruta = public_path($elegido->archivo);
        if (!is_file($ruta)) {
            return null;
        }

        $im = match (strtolower(pathinfo($ruta, PATHINFO_EXTENSION))) {
            'webp' => @imagecreatefromwebp($ruta),
            'png'  => @imagecreatefrompng($ruta),
            'jpg', 'jpeg' => @imagecreatefromjpeg($ruta),
            default => null,
        };

        if ($im) {
            imagealphablending($im, true);
            imagesavealpha($im, true);
        }

        return $im ?: null;
    }
}
