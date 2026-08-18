<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuscarProductos
{
    public static function definition(): array
    {
        return [
            'name' => 'buscar_productos',
            'description' => 'Busca productos en el catálogo real de la tienda por nombre, categoría o palabra clave. Devuelve nombre, precio de venta vigente y stock disponible. Usala SIEMPRE antes de hablar de precios o disponibilidad. Para detalles finos (instrucciones, materiales, preguntas frecuentes, videos) usá después info_producto con el producto_id.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Texto de búsqueda, ej: "colchón 2 plazas", "sommier queen", "almohada"',
                    ],
                ],
                'required' => ['query'],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $query = trim($args['query'] ?? '');
        if ($query === '') {
            return ['error' => 'Falta el texto de búsqueda'];
        }

        $terms = collect(explode(' ', $query))
            ->map(fn ($t) => trim($t))
            ->filter(fn ($t) => mb_strlen($t) >= 3)
            ->take(5);

        $productos = DB::table('productos as p')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->leftJoin('sucursal_articulo as sa', function ($join) {
                $join->on('sa.articulo_id', '=', 'p.idarticulo')->where('sa.activo', 1);
            })
            ->where('p.estado', 'Activo')
            ->where(function ($sub) use ($terms, $query) {
                $sub->where('p.nombre', 'like', "%{$query}%");
                foreach ($terms as $term) {
                    $sub->orWhere('p.nombre', 'like', "%{$term}%")
                        ->orWhere('c.nombre', 'like', "%{$term}%");
                }
            })
            ->groupBy('p.idarticulo', 'p.nombre', 'p.descripcion', 'p.pventa_con_iva', 'c.nombre')
            ->selectRaw('p.idarticulo, p.nombre, p.descripcion, p.pventa_con_iva, c.nombre as categoria, COALESCE(SUM(sa.stock),0) as stock_total')
            ->orderByDesc('stock_total')
            ->limit(8)
            ->get();

        if ($productos->isEmpty()) {
            return ['resultado' => 'Sin coincidencias en el catálogo para esa búsqueda. Probá con otra palabra o consultá al cliente qué busca exactamente.'];
        }

        return [
            'productos' => $productos->map(fn ($p) => [
                'producto_id' => $p->idarticulo,
                'nombre' => $p->nombre,
                'categoria' => $p->categoria,
                'precio' => (float) $p->pventa_con_iva,
                'stock' => (int) $p->stock_total,
                'descripcion' => Str::limit(strip_tags((string) $p->descripcion), 150),
            ])->all(),
        ];
    }
}
