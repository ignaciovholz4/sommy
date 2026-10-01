<?php

namespace App\Http\Controllers\Articulo;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use App\Models\ComboImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
            ->select('p.idarticulo', 'p.nombre', 'p.combo_descuento_pct', 'p.tipo_producto_id', 'p.pventa_con_iva', 'p.pcompra_con_iva')
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

                $c->imagen_combo = DB::table('combo_imagenes')
                    ->where('producto_id', $c->idarticulo)
                    ->orderBy('orden')
                    ->orderBy('id')
                    ->value('path');

                return $c;
            });

        return Datatables::of($combos)
            ->addColumn('imagen_fmt', fn ($c) => $c->imagen_combo
                ? '<img src="' . asset($c->imagen_combo) . '" style="width:56px;height:56px;object-fit:contain;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;">'
                : '<span class="text-muted" style="font-size:0.75rem;">foto del producto</span>')
            ->addColumn('descuento_fmt', fn ($c) => rtrim(rtrim(number_format((float) $c->combo_descuento_pct, 2, ',', '.'), '0'), ',') . '%')
            ->addColumn('relacionados_fmt', fn ($c) => $c->relacionados !== '' ? $c->relacionados : '<span class="text-muted">—</span>')
            ->addColumn('regalos_fmt', fn ($c) => $c->regalos !== '' ? $c->regalos : '<span class="text-muted">—</span>')
            ->addColumn('precio_venta_fmt', fn ($c) => $this->filaPorMedida($c, fn ($v) => '<strong>$' . number_format($v['venta'], 2, ',', '.') . '</strong>'))
            ->addColumn('ganancia_fmt', fn ($c) => $this->filaPorMedida($c, function ($v) {
                if ($v['costo'] <= 0) {
                    return '<span class="text-muted">—</span>';
                }
                $pct = round(($v['ganancia'] / $v['costo']) * 100);
                $color = $v['ganancia'] <= 0 ? '#B91C1C' : ($pct < 20 ? '#92400E' : '#15803D');
                return '<strong style="color:' . $color . ';">$' . number_format($v['ganancia'], 2, ',', '.') . '</strong> '
                    . '<span style="font-size:0.75rem;color:' . $color . ';">(' . ($pct > 0 ? '+' : '') . $pct . '%)</span>';
            }))
            ->addColumn('action', function ($c) {
                return '
                    <button class="btn btn-sm btn-primary" onclick="edit_combo(' . $c->idarticulo . ')" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="delete_combo(' . $c->idarticulo . ')" title="Eliminar"><i class="fas fa-trash"></i></button>
                ';
            })
            ->rawColumns(['imagen_fmt', 'relacionados_fmt', 'regalos_fmt', 'precio_venta_fmt', 'ganancia_fmt', 'action'])
            ->make(true);
    }

    /**
     * Arma, para un combo, el precio de venta / costo / ganancia REAL por cada
     * medida del ancla (igual formula que la vidriera de combos del ecommerce:
     * precio = ancla + relacionados*(1-descuento); el costo NO se descuenta,
     * y los regalos gratis se suman de punta a punta como costo puro porque
     * se entregan sin cobrar). $render decide qué mostrar de cada fila.
     */
    private function filaPorMedida(object $c, \Closure $render): string
    {
        $descuento = (float) $c->combo_descuento_pct / 100;

        $regaloIdsAncla = DB::table('producto_regalos')->where('idarticulo', $c->idarticulo)->pluck('regalo_id');
        $relacionadosIds = DB::table('producto_relacionados')
            ->where('idarticulo', $c->idarticulo)
            ->whereNotIn('relacionado_id', $regaloIdsAncla)
            ->pluck('relacionado_id');

        $relacionados = Articulo::whereIn('idarticulo', $relacionadosIds)
            ->where('estado', 'Activo')
            ->with('combinaciones')
            ->get();

        $regalos = DB::table('producto_regalos as pr')
            ->join('productos as p2', 'p2.idarticulo', '=', 'pr.regalo_id')
            ->where('pr.idarticulo', $c->idarticulo)
            ->select('p2.idarticulo', 'p2.pcompra_con_iva', 'pr.cantidad')
            ->get();
        $costoRegalos = $regalos->sum(fn ($r) => (float) $r->pcompra_con_iva * (int) $r->cantidad);

        // Costo/precio de cada relacionado, matcheado por medida si es un
        // producto con variantes (ej. la base sommier), si no a precio plano.
        $sumarRelacionados = function (?string $anchorMedida) use ($relacionados) {
            $venta = 0.0;
            $costo = 0.0;
            foreach ($relacionados as $rel) {
                $variantes = $rel->combinaciones->where('pventa_variante', '>', 0);
                if ($variantes->isNotEmpty() && $anchorMedida !== null) {
                    $match = $variantes->first(fn ($v) => str_replace(',', '.', trim($v->combinacion)) === $anchorMedida) ?? $variantes->sortBy('pventa_variante')->first();
                    $venta += (float) ($match->pventa_variante ?? 0);
                    $costo += (float) ($match->pcompra_variante ?? 0);
                } else {
                    $venta += (float) $rel->pventa_con_iva;
                    $costo += (float) $rel->pcompra_con_iva;
                }
            }
            return [$venta, $costo];
        };

        $anchor = Articulo::with('combinaciones')->find($c->idarticulo);
        $variantesAncla = $anchor ? $anchor->combinaciones->where('pventa_variante', '>', 0) : collect();

        $filas = collect();
        if ($variantesAncla->isNotEmpty()) {
            foreach ($variantesAncla as $variante) {
                $anchorMedida = str_replace(',', '.', trim(explode('x', $variante->combinacion)[0] ?? ''));
                [$addonsVenta, $addonsCosto] = $sumarRelacionados($anchorMedida);

                $venta = (float) $variante->pventa_variante + $addonsVenta * (1 - $descuento);
                $costo = (float) $variante->pcompra_variante + $addonsCosto + $costoRegalos;

                $filas->push(['medida' => trim($variante->combinacion), 'venta' => $venta, 'costo' => $costo, 'ganancia' => $venta - $costo]);
            }
        } else {
            [$addonsVenta, $addonsCosto] = $sumarRelacionados(null);
            $venta = (float) $c->pventa_con_iva + $addonsVenta * (1 - $descuento);
            $costo = (float) $c->pcompra_con_iva + $addonsCosto + $costoRegalos;
            $filas->push(['medida' => null, 'venta' => $venta, 'costo' => $costo, 'ganancia' => $venta - $costo]);
        }

        return $filas->map(function ($v) use ($render) {
            $contenido = $render($v);
            $prefijo = $v['medida'] ? '<span style="color:#94a3b8;">' . e($v['medida']) . ':</span> ' : '';
            return '<div style="font-size:0.78rem;white-space:nowrap;">' . $prefijo . $contenido . '</div>';
        })->implode('');
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
            'imagenes'            => $this->galeriaCombo((int) $id),
        ]);
    }

    /**
     * Galería del combo (la primera es la que se ve en la vidriera).
     */
    public function imagenes($id)
    {
        return response()->json(['imagenes' => $this->galeriaCombo((int) $id)]);
    }

    /**
     * Sube una o varias imágenes a la galería del combo. Se guardan en
     * public/imagenes/combos/articulo-{id}/ con nombre hasheado, igual que la
     * galería de productos.
     */
    public function subirImagenes(Request $request, $id)
    {
        $request->validate([
            'imagenes'   => 'required|array',
            'imagenes.*' => 'image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $producto = Articulo::findOrFail($id);
        $productoId = (int) $producto->idarticulo;

        $dir = public_path('imagenes/combos/articulo-' . $productoId);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $orden = (int) ComboImagen::where('producto_id', $productoId)->max('orden');

        foreach ($request->file('imagenes') as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $nombre = hash('crc32b', uniqid('', true)) . time() . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($dir, $nombre);

            ComboImagen::create([
                'producto_id' => $productoId,
                'path'        => 'imagenes/combos/articulo-' . $productoId . '/' . $nombre,
                'orden'       => ++$orden,
                'alt'         => 'Combo ' . $producto->nombre,
            ]);
        }

        return response()->json([
            'estado'   => 1,
            'mensaje'  => 'Imágenes subidas',
            'imagenes' => $this->galeriaCombo($productoId),
        ]);
    }

    /**
     * Borra una imagen del combo (fila + archivo físico).
     */
    public function eliminarImagen(Request $request)
    {
        $imagen = ComboImagen::findOrFail((int) $request->input('id'));
        $productoId = (int) $imagen->producto_id;

        if (File::exists(public_path($imagen->path))) {
            File::delete(public_path($imagen->path));
        }
        $imagen->delete();

        return response()->json([
            'estado'   => 1,
            'mensaje'  => 'Imagen eliminada',
            'imagenes' => $this->galeriaCombo($productoId),
        ]);
    }

    /**
     * Reordena la galería: recibe los ids en el orden deseado; el primero es
     * el que queda como imagen del combo en la vidriera.
     */
    public function ordenarImagenes(Request $request)
    {
        $validado = $request->validate([
            'producto_id' => 'required|integer|exists:productos,idarticulo',
            'ids'         => 'required|array',
            'ids.*'       => 'integer',
        ]);

        $productoId = (int) $validado['producto_id'];

        DB::transaction(function () use ($validado, $productoId) {
            foreach (array_values($validado['ids']) as $posicion => $imagenId) {
                ComboImagen::where('producto_id', $productoId)
                    ->where('id', (int) $imagenId)
                    ->update(['orden' => $posicion]);
            }
        });

        return response()->json([
            'estado'   => 1,
            'mensaje'  => 'Orden actualizado',
            'imagenes' => $this->galeriaCombo($productoId),
        ]);
    }

    private function galeriaCombo(int $productoId): array
    {
        return ComboImagen::where('producto_id', $productoId)
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'path', 'alt'])
            ->map(fn ($img) => [
                'id'  => $img->id,
                'url' => asset($img->path),
                'alt' => $img->alt,
            ])
            ->all();
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
