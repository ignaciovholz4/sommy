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
     * Pegado arriba a la izquierda, con una placa suave semitransparente detrás
     * (no un rectángulo blanco duro): tapa cualquier resto de logo/texto falso
     * que Gemini haya dibujado ahí a pesar de la instrucción de dejar esa zona
     * vacía, sin perder el look "flotante" pedido. Detecta automáticamente si
     * esa esquina de la foto es oscura o clara y elige la variante del logo
     * (y el tono de la placa) que corresponda.
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

        $padX = (int) ($lw * 0.45);
        $padY = (int) ($lh * 1.1);
        $this->dibujarPlacaSuave($im, max(0, $lx - $padX), max(0, $ly - $padY), $lw + $padX * 2, $lh + $padY * 2, $fondoOscuro);

        $this->pegarRedimensionado($im, $logo, $lx, $ly, $lw, $lh);
        imagedestroy($logo);
    }

    /** Placa redondeada semitransparente (tono acorde al fondo) para asentar el logo y tapar restos de texto/logo falso debajo. */
    protected function dibujarPlacaSuave($im, int $x, int $y, int $w, int $h, bool $fondoOscuro): void
    {
        $tmp = imagecreatetruecolor($w, $h);
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        imagefilledrectangle($tmp, 0, 0, $w, $h, imagecolorallocatealpha($tmp, 0, 0, 0, 127));
        imagealphablending($tmp, true);

        $color = $fondoOscuro
            ? imagecolorallocatealpha($tmp, 10, 15, 30, 25)
            : imagecolorallocatealpha($tmp, 255, 255, 255, 25);

        $radio = (int) min($h, $w * 0.18);
        $this->rectRedondeado($tmp, 0, 0, $w, $h, $radio, $color);

        imagealphablending($im, true);
        imagecopy($im, $tmp, $x, $y, 0, 0, $w, $h);
        imagedestroy($tmp);
    }

    protected function rectRedondeado($im, int $x0, int $y0, int $w, int $h, int $radio, int $color): void
    {
        $x1 = $x0 + $w;
        $y1 = $y0 + $h;
        imagefilledrectangle($im, $x0 + $radio, $y0, $x1 - $radio, $y1, $color);
        imagefilledrectangle($im, $x0, $y0 + $radio, $x1, $y1 - $radio, $color);
        imagefilledellipse($im, $x0 + $radio, $y0 + $radio, $radio * 2, $radio * 2, $color);
        imagefilledellipse($im, $x1 - $radio, $y0 + $radio, $radio * 2, $radio * 2, $color);
        imagefilledellipse($im, $x0 + $radio, $y1 - $radio, $radio * 2, $radio * 2, $color);
        imagefilledellipse($im, $x1 - $radio, $y1 - $radio, $radio * 2, $radio * 2, $color);
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
