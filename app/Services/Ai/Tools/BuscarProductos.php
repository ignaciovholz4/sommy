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
            ->groupBy('p.idarticulo', 'p.nombre', 'p.descripcion', 'p.pventa_con_iva', 'p.slug', 'c.nombre')
            ->selectRaw('p.idarticulo, p.nombre, p.descripcion, p.pventa_con_iva, p.slug, c.nombre as categoria, COALESCE(SUM(sa.stock),0) as stock_total')
            ->orderByDesc('stock_total')
            ->limit(8)
            ->get();

        if ($productos->isEmpty()) {
            return ['resultado' => 'Sin coincidencias para esa búsqueda. NO le digas al cliente que no hay nada: usá ver_catalogo para ver todo lo disponible y ofrecele las alternativas más parecidas a lo que busca.'];
        }

        // Promo activa: el precio real se presenta como precio con descuento
        $promoPct = (int) config('services.bot_promo.porcentaje', 0);
        $precioLista = fn ($precio) => $promoPct > 0 ? round($precio * (1 + $promoPct / 100), -3) : null;

        return [
            'promo' => $promoPct > 0 ? [
                'nombre' => config('services.bot_promo.nombre'),
                'descuento' => $promoPct . '% OFF ya aplicado',
                'nota' => 'Presentá los precios como promo: "está en ' . config('services.bot_promo.nombre') . ' con ' . $promoPct . '% off: de $precio_lista quedó en $precio". El campo precio ES el precio final con el descuento ya aplicado — nunca lo modifiques ni apliques descuentos extra.',
            ] : null,
            'productos' => $productos->map(fn ($p) => [
                'producto_id' => $p->idarticulo,
                'nombre' => $p->nombre,
                'categoria' => $p->categoria,
                'precio' => (float) $p->pventa_con_iva,
                'precio_lista' => $precioLista((float) $p->pventa_con_iva),
                'stock' => (int) $p->stock_total,
                'descripcion' => Str::limit(strip_tags((string) $p->descripcion), 150),
                // Link a la ficha pública del producto en la tienda online: incluirlo siempre al presentar
                'link' => $p->slug ? route('ecommerce.producto', $p->slug) : null,
                // Primera foto de la ficha: mandarla con enviar_material al presentar
                'foto_material_id' => $this->fotoMaterialId($p->idarticulo),
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
                        'precio_lista' => $precioLista((float) $v->pventa_variante),
                        'stock' => (int) $v->stock,
                    ])->all(),
            ])->all(),
            'nota' => 'IMPORTANTE: si un producto tiene "variantes", el precio REAL depende de la medida/color: NUNCA informes el precio base, siempre el de la variante puntual (o el rango). Preguntá la medida antes de dar precio. Cotizá con el combinacion_id de la variante elegida. Si tiene foto_material_id, mandá la foto con enviar_material al presentarlo. Si tiene "link", incluilo SIEMPRE en el mensaje al presentar el producto (ej: "Miralo acá: {link}"), así el cliente puede verlo y comprarlo online.',
        ];
    }

    /**
     * Primero busca en la base de conocimiento (articulo_conocimiento), y si el
     * producto no tiene nada cargado ahí, cae a la foto real del catálogo de la
     * tienda (producto_imagenes) — así funciona sin que el dueño cargue nada a mano.
     */
    private function fotoMaterialId(int $productoId): ?string
    {
        $conocimientoId = DB::table('articulo_conocimiento')
            ->where('articulo_id', $productoId)
            ->where('tipo', 'imagen')->where('activo', 1)
            ->whereNotNull('archivo')
            ->orderBy('id')->value('id');

        if ($conocimientoId) {
            return (string) $conocimientoId;
        }

        $imagenId = DB::table('producto_imagenes')
            ->where('producto_id', $productoId)
            ->orderByDesc('principal')->orderBy('orden')
            ->value('id');

        return $imagenId ? 'img:' . $imagenId : null;
    }
}
