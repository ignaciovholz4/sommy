<?php

namespace App\Http\Controllers\Publicaciones;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Services\Publicaciones\CopyGeneratorService;
use App\Services\Publicaciones\ImagenIaService;
use App\Services\Publicaciones\MetaPublisherService;
use App\Services\Publicaciones\VideoIaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Estudio de Publicaciones: genera piezas graficas y textos por canal
 * (MercadoLibre, Instagram, Facebook, historias) desde la ficha del producto,
 * con IA para copys (OpenAI) y escenas de producto (Gemini), publicacion
 * directa en Meta y catalogo PDF.
 */
class PublicacionController extends Controller
{
    public function index(ImagenIaService $imagenIa, MetaPublisherService $meta)
    {
        $productos = Articulo::where('estado', 'Activo')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($p) => $this->mapProducto($p))
            ->values();

        $registros = DB::table('publicaciones_registro')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('producto_id');

        $biblioteca = DB::table('publicaciones')
            ->orderByDesc('id')
            ->limit(24)
            ->get();

        $capacidades = [
            'copys'     => (bool) config('services.openai.api_key'),
            'escenas'   => $imagenIa->disponible(),
            'video'     => $imagenIa->disponible(), // Veo usa la misma GEMINI_API_KEY
            'facebook'  => $meta->facebookConfigurado(),
            'instagram' => $meta->instagramConfigurado(),
        ];

        $ajustes = DB::table('publicaciones_ajustes')->first();
        $recursos = DB::table('publicaciones_recursos')->orderBy('tipo')->orderByDesc('id')->get();

        return view('publicaciones.index', [
            'productos'   => $productos,
            'registros'   => $registros,
            'biblioteca'  => $biblioteca,
            'escenas'     => array_keys(ImagenIaService::ESCENAS),
            'escenasTexto'=> ImagenIaService::ESCENAS,
            'capacidades' => $capacidades,
            'ajustes'     => $ajustes,
            'recursos'    => $recursos,
        ]);
    }

    /** Entrenamiento: guarda la voz de marca (textos) y el estilo visual (imágenes). */
    public function guardarAjustes(Request $request)
    {
        $request->validate([
            'voz_marca'     => 'nullable|string|max:3000',
            'estilo_imagen' => 'nullable|string|max:2000',
        ]);

        DB::table('publicaciones_ajustes')->limit(1)->update([
            'voz_marca'     => $request->voz_marca,
            'estilo_imagen' => $request->estilo_imagen,
            'updated_at'    => now(),
        ]);

        return response()->json(['status' => 1]);
    }

    /** Alta de un recurso de marca: imagen/logo (archivo) o prompt/contexto (texto). */
    public function guardarRecurso(Request $request)
    {
        $request->validate([
            'tipo'      => 'required|in:imagen,logo,prompt,contexto',
            'titulo'    => 'required|string|max:120',
            'contenido' => 'required_if:tipo,prompt,contexto|nullable|string|max:3000',
            'archivo'   => 'required_if:tipo,imagen,logo|nullable|file|mimes:jpg,jpeg,png,webp,svg|max:8192',
        ], [
            'titulo.required'       => 'Poné un título al recurso.',
            'contenido.required_if' => 'Escribí el contenido del recurso.',
            'archivo.required_if'   => 'Subí el archivo de imagen.',
        ]);

        $rutaArchivo = null;
        if ($request->hasFile('archivo')) {
            $dir = public_path('imagenes/publicaciones/recursos');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $nombre = $request->tipo . '-' . uniqid() . '.' . $request->file('archivo')->getClientOriginalExtension();
            $request->file('archivo')->move($dir, $nombre);
            $rutaArchivo = 'imagenes/publicaciones/recursos/' . $nombre;
        }

        $id = DB::table('publicaciones_recursos')->insertGetId([
            'tipo'       => $request->tipo,
            'titulo'     => $request->titulo,
            'contenido'  => $request->contenido,
            'archivo'    => $rutaArchivo,
            'activo'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 1, 'id' => $id, 'archivo_url' => $rutaArchivo ? asset($rutaArchivo) : null]);
    }

    public function eliminarRecurso($id)
    {
        $recurso = DB::table('publicaciones_recursos')->find($id);
        if ($recurso) {
            if ($recurso->archivo && is_file(public_path($recurso->archivo))) {
                @unlink(public_path($recurso->archivo));
            }
            DB::table('publicaciones_recursos')->delete($id);
        }

        return response()->json(['status' => 1]);
    }

    /** Copys IA: titulo/descripcion ML, caption IG/FB y mensaje WhatsApp. */
    public function generarCopy(Request $request, CopyGeneratorService $copys)
    {
        $request->validate([
            'producto_id'   => 'required|integer',
            'con_precio'    => 'required|boolean',
            'instrucciones' => 'nullable|string|max:500',
        ]);

        $producto = Articulo::findOrFail($request->producto_id);

        try {
            $textos = $copys->generar($this->mapProducto($producto), (bool) $request->con_precio, (string) $request->instrucciones);
            return response()->json(['status' => 1, 'textos' => $textos]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 0, 'error' => $e->getMessage()], 422);
        }
    }

    /** Escena IA: ambienta la foto real del producto (Gemini). */
    public function generarImagen(Request $request, ImagenIaService $imagenIa)
    {
        $request->validate([
            'producto_id'   => 'required|integer',
            'escena'        => 'required|string|in:' . implode(',', array_keys(ImagenIaService::ESCENAS)),
            'formato'       => 'required|string|max:20',
            'instrucciones' => 'nullable|string|max:500',
            'prompt_libre'  => 'nullable|string|max:2000',
        ]);

        $producto = Articulo::findOrFail($request->producto_id);

        try {
            $resultado = $imagenIa->generarEscena(
                public_path('imagenes/articulos/' . $producto->imagen),
                $request->escena,
                $request->formato,
                (string) $request->instrucciones,
                $request->prompt_libre
            );
            return response()->json(['status' => 1] + $resultado);
        } catch (\Throwable $e) {
            return response()->json(['status' => 0, 'error' => $e->getMessage()], 422);
        }
    }

    /** Video UGC con IA (Veo): una persona presenta y vende el producto a cámara. */
    public function generarVideo(Request $request, VideoIaService $videoIa)
    {
        $request->validate([
            'producto_id'  => 'required|integer',
            'formato'      => 'required|string|max:20',
            'prompt_libre' => 'nullable|string|max:3000',
        ]);

        $producto = Articulo::findOrFail($request->producto_id);

        try {
            $prompt = trim((string) $request->prompt_libre) !== ''
                ? $request->prompt_libre
                : VideoIaService::promptBase($this->mapProducto($producto), true);

            $resultado = $videoIa->generarVideo(
                public_path('imagenes/articulos/' . $producto->imagen),
                $prompt,
                $request->formato
            );
            return response()->json(['status' => 1] + $resultado);
        } catch (\Throwable $e) {
            return response()->json(['status' => 0, 'error' => $e->getMessage()], 422);
        }
    }

    /** Guarda la publicacion (copys + imagen final del canvas) en la biblioteca. */
    public function guardar(Request $request)
    {
        $request->validate([
            'producto_id'   => 'required|integer',
            'formato'       => 'required|string|max:20',
            'estilo'        => 'nullable|string|max:30',
            'titulo_ml'     => 'nullable|string|max:120',
            'desc_ml'       => 'nullable|string',
            'caption'       => 'nullable|string',
            'texto_wa'      => 'nullable|string',
            'imagen_escena' => 'nullable|string|max:255',
            'prompt_escena' => 'nullable|string',
            'imagen_base64' => 'required|string',
            'video_final'   => 'nullable|string|max:255',
        ]);

        if (!preg_match('/^data:image\/png;base64,(.+)$/s', $request->imagen_base64, $m)) {
            return response()->json(['status' => 0, 'error' => 'Imagen invalida'], 422);
        }

        $dir = public_path('imagenes/publicaciones/finales');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $nombre = 'pub-' . $request->producto_id . '-' . uniqid() . '.png';
        file_put_contents($dir . DIRECTORY_SEPARATOR . $nombre, base64_decode($m[1]));

        $id = DB::table('publicaciones')->insertGetId([
            'producto_id'   => $request->producto_id,
            'formato'       => $request->formato,
            'estilo'        => $request->estilo,
            'titulo_ml'     => $request->titulo_ml,
            'desc_ml'       => $request->desc_ml,
            'caption'       => $request->caption,
            'texto_wa'      => $request->texto_wa,
            'imagen_escena' => $request->imagen_escena,
            'prompt_escena' => $request->prompt_escena,
            'imagen_final'  => 'imagenes/publicaciones/finales/' . $nombre,
            'video_final'   => $request->video_final,
            'estado'        => 'borrador',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(['status' => 1, 'id' => $id, 'imagen_url' => asset('imagenes/publicaciones/finales/' . $nombre)]);
    }

    /** Publica una publicacion guardada en Facebook y/o Instagram. */
    public function publicar(Request $request, MetaPublisherService $meta)
    {
        $request->validate([
            'publicacion_id' => 'required|integer',
            'canales'        => 'required|array|min:1',
            'canales.*'      => 'in:facebook,instagram',
        ]);

        $pub = DB::table('publicaciones')->find($request->publicacion_id);
        if (!$pub || !$pub->imagen_final) {
            return response()->json(['status' => 0, 'error' => 'Publicacion no encontrada o sin imagen final'], 404);
        }

        $caption = (string) ($pub->caption ?: $pub->titulo_ml);
        $resultados = [];
        $errores = [];
        $update = [];

        foreach ($request->canales as $canal) {
            try {
                if ($canal === 'facebook') {
                    // Si la publicación tiene video IA, a Facebook va el video; si no, la imagen
                    $update['fb_post_id'] = $pub->video_final
                        ? $meta->publicarFacebookVideo(public_path($pub->video_final), $caption)
                        : $meta->publicarFacebook(public_path($pub->imagen_final), $caption);
                } else {
                    if ($pub->video_final) {
                        throw new \RuntimeException('Los videos a Instagram se suben a mano por ahora: descargalo y subilo como Reel.');
                    }
                    $update['ig_post_id'] = $meta->publicarInstagram(asset($pub->imagen_final), $caption);
                }
                $resultados[] = $canal;
                DB::table('publicaciones_registro')->insert([
                    'producto_id' => $pub->producto_id,
                    'canal'       => $canal,
                    'formato'     => $pub->formato,
                ]);
            } catch (\Throwable $e) {
                $errores[$canal] = $e->getMessage();
            }
        }

        if ($resultados) {
            DB::table('publicaciones')->where('id', $pub->id)->update($update + [
                'estado'       => 'publicada',
                'publicada_at' => now(),
                'updated_at'   => now(),
            ]);
        }

        return response()->json([
            'status'     => $resultados ? 1 : 0,
            'publicados' => $resultados,
            'errores'    => $errores,
        ], $resultados ? 200 : 422);
    }

    /** Catalogo PDF con precios, directo desde el ERP. */
    public function catalogoPdf(Request $request)
    {
        $productos = Articulo::where('estado', 'Activo')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($p) => $this->mapProducto($p) + [
                'imagen_local' => is_file(public_path('imagenes/articulos/' . $p->imagen))
                    ? public_path('imagenes/articulos/' . $p->imagen)
                    : null,
            ])
            ->values();

        $conPrecio = $request->query('precios', '1') === '1';

        $pdf = Pdf::loadView('publicaciones.catalogo-pdf', [
            'productos' => $productos,
            'conPrecio' => $conPrecio,
            'fecha'     => now()->format('m/Y'),
            'logo'      => public_path('imagenes/marca/sommy-logo.png'),
        ])->setPaper('a4');

        return $pdf->stream('catalogo-sommy-' . now()->format('Y-m') . '.pdf');
    }

    /** Registro liviano de "marcado como publicado" (canales manuales). */
    public function registrar(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|integer',
            'canal'       => 'required|in:meli,instagram,facebook,whatsapp,google',
            'formato'     => 'required|string|max:20',
        ]);

        DB::table('publicaciones_registro')->insert([
            'producto_id' => $request->producto_id,
            'canal'       => $request->canal,
            'formato'     => $request->formato,
        ]);

        return response()->json(['status' => 1]);
    }

    protected function mapProducto(Articulo $p): array
    {
        $precio = (float) $p->pventa_con_iva;
        $precioFinal = $p->descuento > 0 ? $precio - ($precio * $p->descuento / 100) : $precio;

        return [
            'id'         => $p->idarticulo,
            'nombre'     => $p->nombre,
            'imagen'     => asset('imagenes/articulos/' . $p->imagen),
            'precio'     => round($precio, 2),
            'precioFinal'=> round($precioFinal, 2),
            'descuento'  => (float) $p->descuento,
            'tipo'       => $p->tipo_colchon ? (Articulo::TIPOS_COLCHON[$p->tipo_colchon] ?? $p->tipo_colchon) : null,
            'firmeza'    => $p->firmeza ? (Articulo::FIRMEZAS[$p->firmeza] ?? $p->firmeza) : null,
            'plazas'     => $p->plazas ? (Articulo::PLAZAS[$p->plazas] ?? $p->plazas) : null,
            'altura'     => $p->altura_cm ? rtrim(rtrim(number_format($p->altura_cm, 1, '.', ''), '0'), '.') : null,
            'pillow'     => (bool) $p->pillow_top,
            'tela'       => $p->tela,
            'garantia'   => $p->garantia_anios,
            'noches'     => $p->noches_prueba,
            'descripcion'=> $p->descripcion,
        ];
    }
}
