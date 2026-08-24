<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;
use Illuminate\Support\Facades\DB;

/**
 * Catalogo completo de productos ofrecidos por el bot (bot_ofrecer=1),
 * agrupado por categoria, con precio y stock. Para que el cliente conozca
 * todo lo disponible y el bot siempre tenga alternativas para ofrecer.
 */
class VerCatalogo
{
    public static function definition(): array
    {
        return [
            'name' => 'ver_catalogo',
            'description' => 'Lista TODO el catálogo que se puede ofrecer, agrupado por categoría, con precio y stock. Usala cuando el cliente quiera conocer todo lo que hay, o cuando una búsqueda no tenga resultados o stock: así siempre podés ofrecer alternativas concretas.',
            'parameters' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $productos = DB::table('productos as p')
            ->leftJoin('categorias as c', 'c.idcategoria', '=', 'p.categoria_id')
            ->leftJoin('sucursal_articulo as sa', function ($join) {
                $join->on('sa.articulo_id', '=', 'p.idarticulo')->where('sa.activo', 1);
            })
            ->where('p.estado', 'Activo')
            ->where('p.bot_ofrecer', 1)
            ->groupBy('p.idarticulo', 'p.nombre', 'p.pventa_con_iva', 'c.nombre')
            ->selectRaw('p.idarticulo, p.nombre, p.pventa_con_iva, c.nombre as categoria, COALESCE(SUM(sa.stock),0) as stock_total')
            ->orderBy('c.nombre')->orderByDesc('stock_total')
            ->limit(40)
            ->get();

        if ($productos->isEmpty()) {
            return ['resultado' => 'No hay productos habilitados para ofrecer en este momento. Derivá a un humano si el cliente quiere comprar.'];
        }

        $variantes = DB::table('producto_combinaciones as pc')
            ->leftJoin('sucursal_combinacion as sc', function ($join) {
                $join->on('sc.combinacion_id', '=', 'pc.idcombinacion')->where('sc.activo', 1);
            })
            ->whereIn('pc.producto_id', $productos->pluck('idarticulo'))
            ->groupBy('pc.producto_id', 'pc.idcombinacion', 'pc.combinacion', 'pc.pventa_variante')
            ->selectRaw('pc.producto_id, pc.idcombinacion, pc.combinacion, pc.pventa_variante, COALESCE(SUM(sc.stock),0) as stock')
            ->get()
            ->groupBy('producto_id');

        $promoPct = (int) config('services.bot_promo.porcentaje', 0);
        $precioLista = fn ($precio) => $promoPct > 0 ? round($precio * (1 + $promoPct / 100), -3) : null;

        $catalogo = $productos->groupBy('categoria')->map(fn ($items) => $items->map(fn ($p) => [
            'producto_id' => $p->idarticulo,
            'nombre'      => $p->nombre,
            'precio_base' => (float) $p->pventa_con_iva,
            'stock'       => (int) $p->stock_total,
            'variantes'   => ($variantes[$p->idarticulo] ?? collect())->map(fn ($v) => [
                'combinacion_id' => $v->idcombinacion,
                'detalle' => $v->combinacion,
                'precio'  => (float) $v->pventa_variante,
                'precio_lista' => $precioLista((float) $v->pventa_variante),
                'stock'   => (int) $v->stock,
            ])->values()->all(),
        ])->values())->toArray();

        return [
            'catalogo' => $catalogo,
            'nota' => 'Si un producto tiene "variantes", el precio real es el de cada medida/color (precio_base es solo referencia: no lo uses). Los de stock 0 se pueden ofrecer como "a pedido". Presentalo resumido, no como lista cruda.',
        ];
    }
}
