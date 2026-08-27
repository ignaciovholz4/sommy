<?php

namespace App\Http\Controllers;

use App\Models\Adjunto;
use App\Models\Compra;
use App\Models\Devolucion;
use App\Models\Envio;
use App\Models\PedidoCompra;
use App\Models\Presupuesto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/**
 * Adjuntos genéricos (remitos, comprobantes, cualquier archivo) pegados a un
 * registro de cualquier módulo. Mismo criterio que NotaController: sin
 * morphTo de Eloquent porque los modelos tienen primary keys no estándar —
 * el mapeo tipo -> modelo/PK se resuelve a mano en $this->tipos().
 *
 * A propósito NO tiene destroy(): lo que se sube queda, no se borra desde
 * la UI (pedido explícito del dueño del negocio).
 */
class AdjuntoController extends Controller
{
    /** tipo => [modelo, columna PK] */
    private function tipos(): array
    {
        return [
            'compra'         => [Compra::class, 'idcompra'],
            'venta'          => [Venta::class, 'idventa'],
            'presupuesto'    => [Presupuesto::class, 'idpresupuesto'],
            'devolucion'     => [Devolucion::class, 'id'],
            'envio'          => [Envio::class, 'id'],
            'pedido_compra'  => [PedidoCompra::class, 'id'],
        ];
    }

    /** Lista de adjuntos de una entidad puntual (usado por el panel embebido). */
    public function lista(Request $request)
    {
        Gate::authorize('haveaccess', 'adjuntos.index');

        $tipo = $request->query('tipo');
        $id   = $request->query('id');

        if (!$tipo || !$id || !isset($this->tipos()[$tipo])) {
            return response()->json(['estado' => 1, 'adjuntos' => []]);
        }

        $adjuntos = Adjunto::where('adjuntable_type', $tipo)
            ->where('adjuntable_id', $id)
            ->orderByDesc('id')
            ->get()
            ->map(fn (Adjunto $a) => [
                'id'            => $a->id,
                'url'           => $a->url,
                'nombre'        => $a->original_name,
                'es_imagen'     => $a->es_imagen,
                'autor'         => optional($a->usuario)->name,
                'fecha'         => $a->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json(['estado' => 1, 'adjuntos' => $adjuntos]);
    }

    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'adjuntos.index');

        $request->validate([
            'tipo'    => 'required|string|in:compra,venta,presupuesto,devolucion,envio,pedido_compra',
            'id'      => 'required|integer',
            'archivo' => 'required|file|max:20480',
        ], [
            'archivo.required' => 'Elegí un archivo.',
            'archivo.max'      => 'El archivo no puede pesar más de 20 MB.',
        ]);

        $tipo = $request->input('tipo');
        $id   = (int) $request->input('id');

        [$modelo, $pk] = $this->tipos()[$tipo];
        if (!$modelo::where($pk, $id)->exists()) {
            return response()->json(['estado' => 0, 'mensaje' => 'No existe ese registro.'], 404);
        }

        $file = $request->file('archivo');
        $path = $file->store('adjuntos/' . $tipo . '/' . $id, 'public');

        $adjunto = Adjunto::create([
            'adjuntable_type' => $tipo,
            'adjuntable_id'   => $id,
            'path'            => $path,
            'original_name'   => $file->getClientOriginalName(),
            'mime'            => $file->getClientMimeType(),
            'size'            => $file->getSize(),
            'user_id'         => Auth::id(),
        ]);

        return response()->json(['estado' => 1, 'adjunto_id' => $adjunto->id]);
    }
}
