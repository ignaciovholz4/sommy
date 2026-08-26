<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Nota;
use App\Models\Proveedor;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Notas recordatorias: sueltas (tablero general) o pegadas a un
 * cliente/proveedor/venta/compra. No usa morphTo de Eloquent porque esos
 * modelos tienen primary keys no estándar (idcliente, idventa, etc.) —
 * el mapeo tipo -> modelo/label se resuelve a mano en $this->tipos().
 */
class NotaController extends Controller
{
    /** tipo => [modelo, columna PK, callback de etiqueta] */
    private function tipos(): array
    {
        return [
            'cliente'   => [Cliente::class, 'idcliente', fn ($c) => trim(collect([$c->nombre, $c->paterno, $c->materno])->filter()->implode(' '))],
            'proveedor' => [Proveedor::class, 'idproveedor', fn ($p) => $p->nombre],
            'venta'     => [Venta::class, 'idventa', fn ($v) => 'Venta #' . ($v->num_folio ?: $v->idventa)],
            'compra'    => [Compra::class, 'idcompra', fn ($c) => 'Compra #' . ($c->num_folio ?: $c->idcompra)],
        ];
    }

    private function etiquetaDe(?string $tipo, ?int $id): ?string
    {
        if (!$tipo || !$id || !isset($this->tipos()[$tipo])) {
            return null;
        }
        [$modelo, $pk, $etiqueta] = $this->tipos()[$tipo];
        $registro = $modelo::where($pk, $id)->first();
        return $registro ? $etiqueta($registro) : null;
    }

    public function index()
    {
        Gate::authorize('haveaccess', 'notas.index');

        $generales = Nota::generales()->orderByDesc('id')->get();

        $pendientesConFecha = Nota::whereNotNull('fecha_recordatorio')
            ->where('completada', false)
            ->orderBy('fecha_recordatorio')
            ->get()
            ->map(function (Nota $n) {
                $n->etiqueta_entidad = $this->etiquetaDe($n->notable_type, $n->notable_id);
                return $n;
            });

        return view('notas.index', compact('generales', 'pendientesConFecha'));
    }

    /** Lista de notas para el tablero general o para una entidad puntual (usado por el panel embebido). */
    public function lista(Request $request)
    {
        Gate::authorize('haveaccess', 'notas.index');

        $tipo = $request->query('tipo');
        $id   = $request->query('id');

        $query = $tipo && $id ? Nota::de($tipo, (int) $id) : Nota::generales();

        $notas = $query->orderByDesc('id')->get()->map(function (Nota $n) {
            return [
                'id'                  => $n->id,
                'contenido'           => $n->contenido,
                'fecha_recordatorio'  => optional($n->fecha_recordatorio)->format('Y-m-d'),
                'completada'          => $n->completada,
                'vencida'             => $n->vencida,
                'autor'               => optional($n->usuario)->name,
                'fecha_creacion'      => $n->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json(['estado' => 1, 'notas' => $notas]);
    }

    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'notas.index');

        $request->validate([
            'contenido'           => 'required|string|max:2000',
            'fecha_recordatorio'  => 'nullable|date',
            'tipo'                => 'nullable|string|in:cliente,proveedor,venta,compra',
            'id'                  => 'nullable|integer',
        ], [
            'contenido.required' => 'Escribí el contenido de la nota.',
        ]);

        $tipo = $request->input('tipo');
        $id   = $request->input('id');

        $nota = Nota::create([
            'contenido'          => $request->contenido,
            'fecha_recordatorio' => $request->fecha_recordatorio,
            'notable_type'       => $tipo && $id ? $tipo : null,
            'notable_id'         => $tipo && $id ? $id : null,
            'user_id'            => Auth::id(),
        ]);

        return response()->json(['estado' => 1, 'nota_id' => $nota->id]);
    }

    public function completar($id)
    {
        Gate::authorize('haveaccess', 'notas.index');

        $nota = Nota::findOrFail($id);
        $nota->completada = !$nota->completada;
        $nota->save();

        return response()->json(['estado' => 1, 'completada' => $nota->completada]);
    }

    public function destroy($id)
    {
        Gate::authorize('haveaccess', 'notas.index');

        Nota::findOrFail($id)->delete();

        return response()->json(['estado' => 1]);
    }
}
