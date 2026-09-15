<?php

namespace App\Http\Controllers\Articulo;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;

/**
 * Gestión de combos desde el panel: elegir un producto existente (ej. un
 * colchón), qué otros productos se pueden sumar con descuento (relacionados,
 * ej. la base) y qué regalo gratis se lleva (ej. 2 almohadas), sin tocar
 * código ni la base de datos a mano.
 */
class ComboController extends Controller
{
    public function index()
    {
        $productos = Articulo::where('estado', 'Activo')->orderBy('nombre')->get(['idarticulo', 'nombre']);

        return view('almacen.combos.index', compact('productos'));
    }

    /**
     * Fuente de datos del DataTable: productos que hoy tienen un combo activo
     * (combo_descuento_pct > 0).
     */
    public function data()
    {
        $combos = DB::table('productos as p')
            ->where('p.estado', 'Activo')
            ->where('p.combo_descuento_pct', '>', 0)
            ->select('p.idarticulo', 'p.nombre', 'p.combo_descuento_pct')
            ->orderBy('p.nombre')
            ->get()
            ->map(function ($c) {
                $c->relacionados = DB::table('producto_relacionados as pr')
                    ->join('productos as p2', 'p2.idarticulo', '=', 'pr.relacionado_id')
                    ->where('pr.idarticulo', $c->idarticulo)
                    ->pluck('p2.nombre')
                    ->implode(', ');

                $c->regalos = DB::table('producto_regalos as pr')
                    ->join('productos as p2', 'p2.idarticulo', '=', 'pr.regalo_id')
                    ->where('pr.idarticulo', $c->idarticulo)
                    ->select('p2.nombre', 'pr.cantidad')
                    ->get()
                    ->map(fn ($r) => $r->nombre . ((int) $r->cantidad > 1 ? " x{$r->cantidad}" : ''))
                    ->implode(', ');

                return $c;
            });

        return Datatables::of($combos)
            ->addColumn('descuento_fmt', fn ($c) => rtrim(rtrim(number_format((float) $c->combo_descuento_pct, 2, ',', '.'), '0'), ',') . '%')
            ->addColumn('relacionados_fmt', fn ($c) => $c->relacionados !== '' ? $c->relacionados : '<span class="text-muted">—</span>')
            ->addColumn('regalos_fmt', fn ($c) => $c->regalos !== '' ? $c->regalos : '<span class="text-muted">—</span>')
            ->addColumn('action', function ($c) {
                return '
                    <button class="btn btn-sm btn-primary" onclick="edit_combo(' . $c->idarticulo . ')" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="delete_combo(' . $c->idarticulo . ')" title="Eliminar"><i class="fas fa-trash"></i></button>
                ';
            })
            ->rawColumns(['relacionados_fmt', 'regalos_fmt', 'action'])
            ->make(true);
    }

    /**
     * Configuración actual de un combo, para precargar el modal de edición.
     */
    public function edit($id)
    {
        $producto = Articulo::findOrFail($id);

        $relacionados = DB::table('producto_relacionados')->where('idarticulo', $id)->pluck('relacionado_id');
        $regalos = DB::table('producto_regalos')->where('idarticulo', $id)->get(['regalo_id', 'cantidad']);

        return response()->json([
            'idarticulo'          => $producto->idarticulo,
            'nombre'              => $producto->nombre,
            'combo_descuento_pct' => (float) $producto->combo_descuento_pct,
            'relacionados'        => $relacionados,
            'regalos'             => $regalos,
        ]);
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'producto_id'            => 'required|exists:productos,idarticulo',
            'combo_descuento_pct'    => 'required|numeric|min:0.01|max:100',
            'relacionados'           => 'nullable|array',
            'relacionados.*'         => 'integer|exists:productos,idarticulo',
            'regalos'                => 'nullable|array',
            'regalos.*.id'           => 'required_with:regalos.*.cantidad|integer|exists:productos,idarticulo',
            'regalos.*.cantidad'     => 'required_with:regalos.*.id|integer|min:1|max:99',
        ]);

        $anchorId = (int) $validado['producto_id'];

        DB::transaction(function () use ($validado, $anchorId) {
            Articulo::where('idarticulo', $anchorId)->update([
                'combo_descuento_pct' => $validado['combo_descuento_pct'],
            ]);

            $this->sincronizarRelacionados($anchorId, (array) ($validado['relacionados'] ?? []));
            $this->sincronizarRegalos($anchorId, (array) ($validado['regalos'] ?? []));
        });

        return response()->json(['estado' => 1, 'mensaje' => 'Combo guardado correctamente']);
    }

    /**
     * "Eliminar" el combo: apaga el armador (combo_descuento_pct = 0) y
     * limpia relacionados/regalos configurados para ese producto. No borra
     * el producto en sí.
     */
    public function destroy(Request $request)
    {
        $id = (int) $request->input('id');

        DB::transaction(function () use ($id) {
            Articulo::where('idarticulo', $id)->update(['combo_descuento_pct' => 0]);
            DB::table('producto_relacionados')->where('idarticulo', $id)->orWhere('relacionado_id', $id)->delete();
            DB::table('producto_regalos')->where('idarticulo', $id)->delete();
        });

        return response()->json(['estado' => 1, 'mensaje' => 'Combo eliminado']);
    }

    // Productos relacionados: la relación es simétrica (si A recomienda B, B también recomienda A).
    private function sincronizarRelacionados(int $articuloId, array $ids): void
    {
        $ids = collect($ids)
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0 && $v !== $articuloId)
            ->unique()
            ->values();

        DB::table('producto_relacionados')
            ->where('idarticulo', $articuloId)
            ->orWhere('relacionado_id', $articuloId)
            ->delete();

        $now = now();
        foreach ($ids as $rid) {
            DB::table('producto_relacionados')->insert([
                ['idarticulo' => $articuloId, 'relacionado_id' => $rid, 'created_at' => $now, 'updated_at' => $now],
                ['idarticulo' => $rid, 'relacionado_id' => $articuloId, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    // Regalos: direccional (comprar el ancla habilita el regalo, no al revés), con cantidad fija por regalo.
    private function sincronizarRegalos(int $articuloId, array $regalos): void
    {
        DB::table('producto_regalos')->where('idarticulo', $articuloId)->delete();

        $now = now();
        $vistos = [];
        foreach ($regalos as $r) {
            $regaloId = (int) ($r['id'] ?? 0);
            $cantidad = max(1, (int) ($r['cantidad'] ?? 1));
            if ($regaloId <= 0 || $regaloId === $articuloId || in_array($regaloId, $vistos, true)) {
                continue;
            }
            $vistos[] = $regaloId;
            DB::table('producto_regalos')->insert([
                'idarticulo' => $articuloId, 'regalo_id' => $regaloId, 'cantidad' => $cantidad,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
