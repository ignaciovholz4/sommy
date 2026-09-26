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
    /** Pegado con fondo transparente real (sin placa blanca), arriba a la izquierda. */
    public function pegarConPlaca($im, int $w, int $h): void
    {
        $logo = $this->cargarLogo(false);
        if (!$logo) {
            return;
        }

        $lw = (int) ($w * 0.30);
        $lh = (int) ($lw * imagesy($logo) / imagesx($logo));
        $lx = (int) ($w * 0.055);
        $ly = (int) ($h * 0.045);

        $this->pegarRedimensionado($im, $logo, $lx, $ly, $lw, $lh);
        imagedestroy($logo);
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
