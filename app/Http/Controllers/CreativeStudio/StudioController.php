<?php

namespace App\Http\Controllers\CreativeStudio;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Publicaciones\PublicacionController;
use App\Models\Articulo;
use App\Services\CreativeStudio\ProductLibraryService;
use App\Services\Publicaciones\ImagenIaService;
use App\Services\Publicaciones\MetaPublisherService;
use Illuminate\Http\Request;

/**
 * Modo "Create" (pro) de Sommy Creative Studio: control fino de objetivo,
 * intensidad comercial, escena, iluminación, cámara, composición y zona de
 * texto. Comparte el mismo motor (PromptBuilder + GeminiImageProvider) y
 * los mismos endpoints de guardar/publicar/programar que el chat rápido de
 * /publicaciones — no se duplica esa lógica, solo la pantalla de armado.
 */
class StudioController extends Controller
{
    public function create(ImagenIaService $imagenIa, MetaPublisherService $meta, ProductLibraryService $productos)
    {
        $ctrl = app(PublicacionController::class);

        $productosData = Articulo::where('estado', 'Activo')
            ->orderBy('nombre')
            ->get()
            ->map(function (Articulo $p) use ($ctrl, $productos) {
                $ficha = $ctrl->mapProducto($p);
                $ficha['galeria'] = $productos->imagenes($p)->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => asset($img->path),
                    'angulo' => $img->angulo,
                    'principal' => (bool) $img->principal,
                ])->values();
                return $ficha;
            })
            ->values();

        $capacidades = [
            'escenas'   => $imagenIa->disponible(),
            'facebook'  => $meta->facebookConfigurado(),
            'instagram' => $meta->instagramConfigurado(),
        ];

        return view('creative-studio.create', [
            'productos'   => $productosData,
            'capacidades' => $capacidades,
        ]);
    }

    public function generar(Request $request, ImagenIaService $imagenIa, ProductLibraryService $productos)
    {
        $request->validate([
            'producto_id'  => 'required|integer',
            'formato'      => 'required|string|in:feed,story,ml',
            'cantidad'     => 'nullable|integer|min:1|max:4',
            'objetivo'     => 'nullable|string|max:30',
            'intensidad'   => 'nullable|string|max:30',
            'escena'       => 'nullable|string|max:30',
            'densidad'     => 'nullable|string|max:30',
            'iluminacion'  => 'nullable|string|max:30',
            'camara'       => 'nullable|string|max:30',
            'composicion'  => 'nullable|string|max:40',
            'zona_texto'   => 'nullable|string|max:30',
            'personas'     => 'nullable|string|max:20',
        ]);

        $producto = Articulo::findOrFail($request->producto_id);
        $rutaFoto = $productos->rutaImagenPrincipal($producto);
        if ($rutaFoto === null) {
            return response()->json(['status' => 0, 'error' => 'Este producto no tiene ninguna foto real cargada.'], 422);
        }

        try {
            $resultados = $imagenIa->generarVariantesStudio(
                $rutaFoto,
                $request->formato,
                (int) $request->input('cantidad', 2),
                $request->only(['objetivo', 'intensidad', 'escena', 'densidad', 'iluminacion', 'camara', 'composicion', 'zona_texto', 'personas'])
            );
            return response()->json(['status' => 1, 'variantes' => $resultados]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 0, 'error' => $e->getMessage()], 422);
        }
    }
}
