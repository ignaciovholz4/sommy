<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\GastoCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * CRUD de categorías de gastos (alquiler, servicios, fletes, sueldos, etc.).
 * Se maneja por AJAX desde el modal de categorías en el index de gastos.
 */
class GastoCategoriaController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.index');

        $categorias = GastoCategoria::with('padre')
            ->orderBy('activo', 'desc')
            ->orderBy('nombre')
            ->get()
            ->map(function ($c) {
                return [
                    'id'       => $c->id,
                    'nombre'   => $c->nombre,
                    'padre_id' => $c->padre_id,
                    'padre'    => $c->padre?->nombre,
                    'activo'   => (bool) $c->activo,
                ];
            });

        return response()->json(['estado' => 1, 'categorias' => $categorias]);
    }

    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.categorias');

        $request->validate([
            'nombre'   => 'required|string|max:120',
            'padre_id' => 'nullable|exists:gasto_categorias,id',
        ], [
            'nombre.required' => 'Ingresá el nombre de la categoría.',
        ]);

        $categoria = GastoCategoria::create([
            'nombre'   => $request->nombre,
            'padre_id' => $request->padre_id,
            'activo'   => true,
        ]);

        return response()->json(['estado' => 1, 'mensaje' => 'Categoría creada correctamente.', 'categoria' => $categoria]);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.categorias');

        $categoria = GastoCategoria::findOrFail($id);

        $request->validate([
            'nombre'   => 'required|string|max:120',
            'padre_id' => 'nullable|exists:gasto_categorias,id',
        ]);

        if ((int) $request->padre_id === (int) $id) {
            return response()->json(['estado' => 0, 'mensaje' => 'Una categoría no puede ser su propia madre.']);
        }

        $categoria->update([
            'nombre'   => $request->nombre,
            'padre_id' => $request->padre_id,
            'activo'   => $request->boolean('activo', $categoria->activo),
        ]);

        return response()->json(['estado' => 1, 'mensaje' => 'Categoría actualizada correctamente.']);
    }

    public function destroy($id)
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.categorias');

        $categoria = GastoCategoria::withCount(['gastos', 'hijas'])->findOrFail($id);

        if ($categoria->gastos_count > 0 || $categoria->hijas_count > 0) {
            // Tiene historia: no se borra, se desactiva
            $categoria->update(['activo' => false]);

            return response()->json(['estado' => 1, 'mensaje' => 'La categoría tiene gastos o subcategorías: se desactivó en lugar de borrarse.']);
        }

        $categoria->delete();

        return response()->json(['estado' => 1, 'mensaje' => 'Categoría eliminada correctamente.']);
    }
}
