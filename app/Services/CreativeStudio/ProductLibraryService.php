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

    /**
     * Hasta 2 ángulos reales EXTRA del mismo producto (de su propia galería
     * producto_imagenes), sin repetir la foto principal ya usada — se mandan
     * a la IA como fidelidad adicional para que acierte mejor forma/textura.
     *
     * @return array<int, string> rutas absolutas
     */
    public function rutasAngulosExtra(?int $productoId, string $rutaFotoPrincipal, int $max = 2): array
    {
        if (!$productoId) {
            return [];
        }

        $galeria = DB::table('producto_imagenes')
            ->where('producto_id', $productoId)
            ->where('tipo', 'imagen')
            ->orderByDesc('principal')->orderBy('orden')
            ->get(['path']);

        $rutas = $galeria->map(fn ($g) => public_path($g->path))
            ->filter(fn ($ruta) => is_file($ruta) && $ruta !== $rutaFotoPrincipal)
            ->unique()->take($max)->values()->all();

        return $rutas;
    }

    /**
     * Foto real de la base/sommier de Sommy (si está cargada en el catálogo), para
     * que la IA use la base real en vez de inventar un sommier/cama genérica.
     * Nunca inventa: si no encuentra ningún producto de sommier con foto, null.
     */
    public function rutaSommierReal(): ?string
    {
        $sommier = \App\Models\Articulo::where('estado', 'Activo')
            ->where('nombre', 'like', '%sommier%')
            ->whereNotNull('imagen')
            ->first();

        if (!$sommier) {
            return null;
        }

        $ruta = public_path('imagenes/articulos/' . $sommier->imagen);

        return is_file($ruta) ? $ruta : null;
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
