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

        $catalogo = $productos->groupBy('categoria')->map(fn ($items) => $items->map(fn ($p) => [
            'producto_id' => $p->idarticulo,
            'nombre'      => $p->nombre,
            'precio'      => (float) $p->pventa_con_iva,
            'stock'       => (int) $p->stock_total,
        ])->values())->toArray();

        return [
            'catalogo' => $catalogo,
            'nota' => 'Este es todo el catálogo ofrecible. Los de stock 0 se pueden mencionar como "a pedido / consultar demora" pero priorizá los que tienen stock. Presentalo resumido, no como lista cruda.',
        ];
    }
}
