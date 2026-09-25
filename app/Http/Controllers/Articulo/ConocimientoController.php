<?php

namespace App\Http\Controllers\Articulo;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\ArticuloConocimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Base de conocimiento interna por producto: instrucciones, características,
 * FAQs y archivos multimedia que explican el producto. NO se muestra en el
 * ecommerce: es contexto para el bot del CRM y el Estudio de Publicaciones.
 */
class ConocimientoController extends Controller
{
    public function index($id)
    {
        $articulo = Articulo::findOrFail($id);

        $items = ArticuloConocimiento::where('articulo_id', $id)
            ->orderByDesc('prioridad')
            ->orderByDesc('id')
            ->get();

        return view('almacen.articulo.conocimiento', [
            'articulo' => $articulo,
            'items'    => $items,
            'tipos'    => ArticuloConocimiento::TIPOS,
        ]);
    }

    /** Edita titulo/contenido de un item de conocimiento (el archivo no cambia). */
    public function update(Request $request, $itemId)
    {
        $item = ArticuloConocimiento::findOrFail($itemId);

        $request->validate([
            'titulo'    => 'required|string|max:150',
            'contenido' => 'nullable|string|max:8000',
            'prioridad' => 'nullable|integer|min:0|max:10',
        ]);

        $item->titulo = trim($request->titulo);
        if ($item->esTexto()) {
            $item->contenido = trim((string) $request->contenido);
        }
        $item->prioridad = (int) $request->input('prioridad', $item->prioridad);
        $item->save();

        return response()->json(['estado' => 1]);
    }

    /** Tilda/destilda el producto para que el bot de ventas lo ofrezca. */
    public function toggleBot($id)
    {
        $articulo = Articulo::findOrFail($id);
        $articulo->bot_ofrecer = !$articulo->bot_ofrecer;
        $articulo->save();

        return response()->json(['estado' => 1, 'bot_ofrecer' => (bool) $articulo->bot_ofrecer]);
    }

    public function store(Request $request, $id)
    {
        Articulo::findOrFail($id);

        $request->validate([
            'tipo'        => 'required|in:' . implode(',', array_keys(ArticuloConocimiento::TIPOS)),
            'titulo'      => 'required|string|max:150',
            'contenido'   => 'required_if:tipo,' . implode(',', ArticuloConocimiento::TIPOS_TEXTO) . '|nullable|string|max:8000',
            'archivo'     => 'required_if:tipo,video,audio,documento|nullable|file|max:51200|mimes:jpg,jpeg,png,webp,mp4,mov,webm,mp3,wav,ogg,m4a,pdf',
            'archivos'    => 'required_if:tipo,imagen|nullable|array|max:30',
            'archivos.*'  => 'file|max:51200|mimes:jpg,jpeg,png,webp',
            'prioridad'   => 'nullable|integer|min:0|max:10',
        ], [
            'titulo.required'        => 'Poné un título.',
            'contenido.required_if'  => 'Escribí el contenido.',
            'archivo.required_if'    => 'Subí el archivo.',
            'archivos.required_if'   => 'Subí al menos una imagen.',
            'archivo.max'            => 'El archivo puede pesar hasta 50 MB.',
            'archivo.mimes'          => 'Formatos permitidos: imágenes, mp4/mov/webm, mp3/wav/ogg/m4a o PDF.',
            'archivos.*.mimes'       => 'Las imágenes tienen que ser jpg, png o webp.',
        ]);

        $disk = config('services.conocimiento.disk', 'public');
        $prioridad = (int) $request->input('prioridad', 0);

        // Tipo imagen: multi-carga real — cada archivo se guarda como su propia fila,
        // así todas quedan disponibles como fidelidad real para el Estudio de Publicaciones.
        if ($request->tipo === 'imagen' && $request->hasFile('archivos')) {
            $archivos = $request->file('archivos');
            $total = count($archivos);
            foreach ($archivos as $i => $archivo) {
                $ruta = $archivo->store('conocimiento/articulo-' . $id, $disk);
                ArticuloConocimiento::create([
                    'articulo_id' => $id,
                    'tipo'        => 'imagen',
                    'titulo'      => $total > 1 ? $request->titulo . ' (' . ($i + 1) . '/' . $total . ')' : $request->titulo,
                    'contenido'   => null,
                    'archivo'     => $ruta,
                    'mime'        => $archivo->getMimeType(),
                    'activo'      => true,
                    'prioridad'   => $prioridad,
                ]);
            }

            return back()->with('con_ok', $total > 1 ? "Se agregaron {$total} imágenes al conocimiento del producto." : 'Se agregó "' . $request->titulo . '" al conocimiento del producto.');
        }

        $ruta = null;
        $mime = null;
        if ($request->hasFile('archivo')) {
            $ruta = $request->file('archivo')->store('conocimiento/articulo-' . $id, $disk);
            $mime = $request->file('archivo')->getMimeType();
        }

        ArticuloConocimiento::create([
            'articulo_id' => $id,
            'tipo'        => $request->tipo,
            'titulo'      => $request->titulo,
            'contenido'   => $request->contenido,
            'archivo'     => $ruta,
            'mime'        => $mime,
            'activo'      => true,
            'prioridad'   => $prioridad,
        ]);

        return back()->with('con_ok', 'Se agregó "' . $request->titulo . '" al conocimiento del producto.');
    }

    public function destroy($itemId)
    {
        $item = ArticuloConocimiento::findOrFail($itemId);

        if ($item->archivo) {
            Storage::disk(config('services.conocimiento.disk', 'public'))->delete($item->archivo);
        }
        $item->delete();

        return response()->json(['status' => 1]);
    }
}
