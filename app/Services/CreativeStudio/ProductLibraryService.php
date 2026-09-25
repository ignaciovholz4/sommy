<?php

namespace App\Services\CreativeStudio;

use App\Models\Articulo;
use Illuminate\Support\Collection;

/**
 * Envoltorio de lectura sobre el catálogo real (Articulo + producto_imagenes +
 * articulo_conocimiento). No inventa ni edita nada: los datos del ERP son
 * siempre la fuente de verdad, nunca editables por la IA.
 */
class ProductLibraryService
{
    /** Galería real del producto (tabla producto_imagenes), imágenes primero, en orden. */
    public function imagenes(Articulo $producto): Collection
    {
        return $producto->imagenes()
            ->where('tipo', 'imagen')
            ->orderByDesc('principal')
            ->orderBy('orden')
            ->get();
    }

    /**
     * Ruta absoluta a la foto de referencia principal: la de la galería marcada
     * "principal", si no la primera de la galería, si no la columna legacy
     * `imagen`. Es la que se manda a Gemini como producto fiel.
     */
    public function rutaImagenPrincipal(Articulo $producto): ?string
    {
        $galeria = $this->imagenes($producto)->first();
        if ($galeria) {
            $ruta = public_path($galeria->path);
            if (is_file($ruta)) {
                return $ruta;
            }
        }

        if ($producto->imagen) {
            $ruta = public_path('imagenes/articulos/' . $producto->imagen);
            if (is_file($ruta)) {
                return $ruta;
            }
        }

        return null;
    }

    /** Características/reglas verificadas del producto (articulo_conocimiento activo). */
    public function conocimiento(Articulo $producto): Collection
    {
        return \Illuminate\Support\Facades\DB::table('articulo_conocimiento')
            ->where('articulo_id', $producto->idarticulo)
            ->where('activo', 1)
            ->whereIn('tipo', ['instrucciones', 'caracteristicas', 'faq', 'nota'])
            ->orderByDesc('prioridad')
            ->get(['tipo', 'titulo', 'contenido']);
    }
}
