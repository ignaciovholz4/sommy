<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;
use App\Services\Ai\Concerns\ResuelveMaterialProducto;
use Illuminate\Support\Facades\DB;

/**
 * Catalogo completo de productos ofrecidos por el bot (bot_ofrecer=1),
 * agrupado por categoria, con precio, stock y su foto/video. Para que el
 * cliente conozca todo lo disponible y el bot siempre tenga alternativas
 * para ofrecer.
 */
class VerCatalogo
{
    use ResuelveMaterialProducto;

    public static function definition(): array
    {
        return [
            'name' => 'ver_catalogo',
            'description' => 'Lista TODO el catálogo que se puede ofrecer, agrupado por categoría, con precio, stock y su foto/video. Usala cuando el cliente quiera conocer todo lo que hay, o cuando una búsqueda no tenga resultados o stock: así siempre podés ofrecer alternativas concretas.',
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
            'foto_material_id' => $this->materialId($p->idarticulo, 'imagen'),
            'video_material_id' => $this->materialId($p->idarticulo, 'video'),
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
            'nota' => 'Si un producto tiene "variantes", el precio real es el de cada medida/color (precio_base es solo referencia: no lo uses). Los de stock 0 se pueden ofrecer como "a pedido". Presentalo resumido, no como lista cruda. Si el cliente pidió ver una categoría completa (ej "todo el catálogo de colchones"), mostrale TODOS los productos de esa categoría que te devolvió esta herramienta, sin quedarte en 2 o 3 y sin elegir vos cuáles dejar afuera. Para cada producto que tenga foto_material_id, LLAMÁ a la herramienta enviar_material con ese id, y poné el nombre del producto y su gancho de venta en el parámetro "mensaje" de ESA MISMA llamada — así viaja como pie de foto. NUNCA repitas después esa misma descripción en un mensaje de texto aparte: la foto con su mensaje YA es la presentación de ese producto, no la dupliques. Mandá también el video si tiene video_material_id. Está PROHIBIDO escribir la foto como texto, markdown tipo ![...](...), link o nombre de archivo en tu mensaje: eso no le llega al cliente como imagen, tenés que usar la herramienta enviar_material sí o sí. Al final, si hace falta, cerrá con una sola pregunta corta para que el cliente elija — no repitas el catálogo en texto.',
        ];
    }
}
