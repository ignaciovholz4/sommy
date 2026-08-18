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
            ->orderByDesc('id')
            ->get();

        return view('almacen.articulo.conocimiento', [
            'articulo' => $articulo,
            'items'    => $items,
            'tipos'    => ArticuloConocimiento::TIPOS,
        ]);
    }

    public function store(Request $request, $id)
    {
        Articulo::findOrFail($id);

        $request->validate([
            'tipo'      => 'required|in:' . implode(',', array_keys(ArticuloConocimiento::TIPOS)),
            'titulo'    => 'required|string|max:150',
            'contenido' => 'required_if:tipo,' . implode(',', ArticuloConocimiento::TIPOS_TEXTO) . '|nullable|string|max:8000',
            'archivo'   => 'required_if:tipo,imagen,video,audio,documento|nullable|file|max:51200|mimes:jpg,jpeg,png,webp,mp4,mov,webm,mp3,wav,ogg,m4a,pdf',
        ], [
            'titulo.required'       => 'Poné un título.',
            'contenido.required_if' => 'Escribí el contenido.',
            'archivo.required_if'   => 'Subí el archivo.',
            'archivo.max'           => 'El archivo puede pesar hasta 50 MB.',
            'archivo.mimes'         => 'Formatos permitidos: imágenes, mp4/mov/webm, mp3/wav/ogg/m4a o PDF.',
        ]);

        $ruta = null;
        $mime = null;
        if ($request->hasFile($archivoCampo = 'archivo')) {
            $disk = config('services.conocimiento.disk', 'public');
            $ruta = $request->file($archivoCampo)->store('conocimiento/articulo-' . $id, $disk);
            $mime = $request->file($archivoCampo)->getMimeType();
        }

        ArticuloConocimiento::create([
            'articulo_id' => $id,
            'tipo'        => $request->tipo,
            'titulo'      => $request->titulo,
            'contenido'   => $request->contenido,
            'archivo'     => $ruta,
            'mime'        => $mime,
            'activo'      => true,
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
