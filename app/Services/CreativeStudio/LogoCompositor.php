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
     * Pegado con fondo 100% transparente (sin placa ni plate detrás), arriba a
     * la izquierda, recortado al contenido real del logo (sin el margen vacío
     * que trae el archivo original) para que quede bien encuadrado en la
     * esquina y no "flotando" con aire de más. Detecta automáticamente si esa
     * esquina de la foto es oscura o clara y elige la variante del logo que
     * corresponda.
     */
    public function pegarConPlaca($im, int $w, int $h): void
    {
        $lx = (int) ($w * 0.055);
        $ly = (int) ($h * 0.045);
        $lwMax = (int) ($w * 0.30);

        $fondoOscuro = $this->esZonaOscura($im, $lx, $ly, $lwMax, (int) ($lwMax * 0.4));

        $logo = $this->cargarLogo($fondoOscuro);
        if (!$logo) {
            return;
        }

        $recorte = $this->recortarAlContenido($logo);
        $lh = (int) ($lwMax * $recorte['h'] / $recorte['w']);

        $tmp = imagecreatetruecolor($recorte['w'], $recorte['h']);
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        imagecopy($tmp, $logo, 0, 0, $recorte['x'], $recorte['y'], $recorte['w'], $recorte['h']);

        $this->pegarRedimensionado($im, $tmp, $lx, $ly, $lwMax, $lh);
        imagedestroy($tmp);
        imagedestroy($logo);
    }

    /** Calcula el bounding box real (no transparente) del logo, para recortar el aire vacío del archivo original. */
    protected function recortarAlContenido($logo): array
    {
        $w = imagesx($logo);
        $h = imagesy($logo);
        $minX = $w; $minY = $h; $maxX = 0; $maxY = 0;
        $paso = max(1, (int) min($w, $h) / 200);

        for ($x = 0; $x < $w; $x += $paso) {
            for ($y = 0; $y < $h; $y += $paso) {
                $alpha = (imagecolorat($logo, $x, $y) >> 24) & 0x7F;
                if ($alpha < 120) { // no totalmente transparente
                    $minX = min($minX, $x); $maxX = max($maxX, $x);
                    $minY = min($minY, $y); $maxY = max($maxY, $y);
                }
            }
        }

        if ($maxX <= $minX || $maxY <= $minY) {
            return ['x' => 0, 'y' => 0, 'w' => $w, 'h' => $h];
        }

        return ['x' => $minX, 'y' => $minY, 'w' => $maxX - $minX + 1, 'h' => $maxY - $minY + 1];
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
