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
            ->leftJoin('categorias as c', 'c.idcategoria', '=', 'p.categoria_id')
            ->leftJoin('sucursal_articulo as sa', function ($join) {
                $join->on('sa.articulo_id', '=', 'p.idarticulo')->where('sa.activo', 1);
            })
            ->where('p.estado', 'Activo')
            ->where('p.bot_ofrecer', 1) // el bot solo ofrece lo tildado por el dueño
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
            return ['resultado' => 'Sin coincidencias para esa búsqueda. NO le digas al cliente que no hay nada: usá ver_catalogo para ver todo lo disponible y ofrecele las alternativas más parecidas a lo que busca.'];
        }

        return [
            'productos' => $productos->map(fn ($p) => [
                'producto_id' => $p->idarticulo,
                'nombre' => $p->nombre,
                'categoria' => $p->categoria,
                'precio' => (float) $p->pventa_con_iva,
                'stock' => (int) $p->stock_total,
                'descripcion' => Str::limit(strip_tags((string) $p->descripcion), 150),
                // Primera foto de la ficha: mandarla con enviar_material al presentar
                'foto_material_id' => DB::table('articulo_conocimiento')
                    ->where('articulo_id', $p->idarticulo)
                    ->where('tipo', 'imagen')->where('activo', 1)
                    ->whereNotNull('archivo')
                    ->orderBy('id')->value('id'),
                // Variantes: medidas/colores con SU precio y SU stock (el precio real vive acá)
                'variantes' => DB::table('producto_combinaciones as pc')
                    ->leftJoin('sucursal_combinacion as sc', function ($join) {
                        $join->on('sc.combinacion_id', '=', 'pc.idcombinacion')->where('sc.activo', 1);
                    })
                    ->where('pc.producto_id', $p->idarticulo)
                    ->groupBy('pc.idcombinacion', 'pc.combinacion', 'pc.pventa_variante')
                    ->selectRaw('pc.idcombinacion, pc.combinacion, pc.pventa_variante, COALESCE(SUM(sc.stock),0) as stock')
                    ->get()
                    ->map(fn ($v) => [
                        'combinacion_id' => $v->idcombinacion,
                        'detalle' => $v->combinacion, // medida / color / tamaño
                        'precio' => (float) $v->pventa_variante,
                        'stock' => (int) $v->stock,
                    ])->all(),
            ])->all(),
            'nota' => 'IMPORTANTE: si un producto tiene "variantes", el precio REAL depende de la medida/color: NUNCA informes el precio base, siempre el de la variante puntual (o el rango). Preguntá la medida antes de dar precio. Cotizá con el combinacion_id de la variante elegida. Si tiene foto_material_id, mandá la foto con enviar_material al presentarlo.',
        ];
    }
}
