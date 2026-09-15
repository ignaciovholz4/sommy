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
            'description' => 'Busca productos en el catálogo real de la tienda por nombre, categoría o palabra clave. Devuelve nombre, precio de venta vigente, stock disponible y si tiene un combo armable con descuento y regalos. Usala SIEMPRE antes de hablar de precios, disponibilidad o promos/combos. Para detalles finos (instrucciones, materiales, preguntas frecuentes, videos) usá después info_producto con el producto_id.',
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
            ->groupBy('p.idarticulo', 'p.nombre', 'p.descripcion', 'p.pventa_con_iva', 'p.slug', 'c.nombre', 'p.combo_descuento_pct')
            ->selectRaw('p.idarticulo, p.nombre, p.descripcion, p.pventa_con_iva, p.slug, p.combo_descuento_pct, c.nombre as categoria, COALESCE(SUM(sa.stock),0) as stock_total')
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
                // Foto y video de la ficha (el de mayor prioridad cargada en Conocimiento):
                // mandarlos con enviar_material al presentar el producto
                'foto_material_id' => $this->materialId($p->idarticulo, 'imagen'),
                'video_material_id' => $this->materialId($p->idarticulo, 'video'),
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
                'combo' => $this->comboInfo((int) $p->idarticulo, (float) $p->combo_descuento_pct),
            ])->all(),
            'nota' => 'IMPORTANTE: si un producto tiene "variantes", el precio REAL depende de la medida/color: NUNCA informes el precio base, siempre el de la variante puntual (o el rango). Preguntá la medida antes de dar precio. Cotizá con el combinacion_id de la variante elegida. Cuando presentes un producto (sobre todo si mostraste varias opciones), mandá SIEMPRE su foto con enviar_material usando foto_material_id, y si tiene video_material_id mandá también el video: cada colchón se acompaña con su foto y su video si existe. Si el cliente preguntó por un tipo o categoría completa (espuma, resortes, todos los colchones), mostrale TODOS los productos que te devolvió esta búsqueda, no elijas vos un subconjunto. Si tiene "link", incluilo SIEMPRE en el mensaje al presentar el producto (ej: "Miralo acá: {link}"), así el cliente puede verlo y comprarlo online. Si un producto trae "combo", SIEMPRE ofrecé armar el combo cuando presentes ese producto o cuando el cliente pregunte por promos/combos: contale qué se suma con descuento y qué se lleva de regalo, usando EXACTAMENTE los nombres y precios que vienen ahí, nunca calculados por vos. Para cotizarlo, cotizá el producto ancla y además cada relacionado/regalo que el cliente confirme sumar, con sus propios producto_id — el sistema aplica el descuento y el regalo a $0 automáticamente al detectar el combo.',
        ];
    }

    /**
     * Combo real armado por el dueño desde el panel (almacen/combos): el
     * producto ancla (ej. un colchón) permite sumar sus "relacionados" con
     * descuento y se lleva sus "regalos" gratis. El precio con descuento se
     * calcula ACÁ (no lo calcula el LLM) para que sea un monto real que la
     * guarda anti-precios-inventados de AiAgentService reconozca como válido.
     */
    private function comboInfo(int $anchorId, float $descuentoPct): ?array
    {
        if ($descuentoPct <= 0) {
            return null;
        }

        $regaloIds = DB::table('producto_regalos')->where('idarticulo', $anchorId)->pluck('regalo_id');

        $regalos = DB::table('producto_regalos as pr')
            ->join('productos as p', 'p.idarticulo', '=', 'pr.regalo_id')
            ->where('pr.idarticulo', $anchorId)
            ->where('p.estado', 'Activo')
            ->select('p.idarticulo', 'p.nombre', 'pr.cantidad')
            ->get()
            ->map(fn ($r) => [
                'producto_id' => $r->idarticulo,
                'nombre' => $r->nombre,
                'cantidad' => (int) $r->cantidad,
            ])->all();

        $relacionados = DB::table('producto_relacionados as prel')
            ->join('productos as p', 'p.idarticulo', '=', 'prel.relacionado_id')
            ->where('prel.idarticulo', $anchorId)
            ->where('p.estado', 'Activo')
            ->whereNotIn('prel.relacionado_id', $regaloIds)
            ->select('p.idarticulo', 'p.nombre', 'p.pventa_con_iva')
            ->get();

        if (empty($regalos) && $relacionados->isEmpty()) {
            return null;
        }

        $relacionadosOut = $relacionados->map(function ($r) use ($descuentoPct) {
            $variantes = DB::table('producto_combinaciones')
                ->where('producto_id', $r->idarticulo)
                ->where('pventa_variante', '>', 0)
                ->get();

            if ($variantes->isNotEmpty()) {
                return [
                    'producto_id' => $r->idarticulo,
                    'nombre' => $r->nombre,
                    'variantes' => $variantes->map(fn ($v) => [
                        'combinacion_id' => $v->idcombinacion,
                        'detalle' => $v->combinacion,
                        'precio' => round((float) $v->pventa_variante * (1 - $descuentoPct / 100), 2),
                    ])->values()->all(),
                ];
            }

            return [
                'producto_id' => $r->idarticulo,
                'nombre' => $r->nombre,
                'precio' => round((float) $r->pventa_con_iva * (1 - $descuentoPct / 100), 2),
            ];
        })->values()->all();

        return [
            'descuento_pct' => $descuentoPct,
            'relacionados' => $relacionadosOut,
            'regalos' => $regalos,
        ];
    }

    /**
     * Primero busca en la base de conocimiento (articulo_conocimiento, el de
     * mayor prioridad cargada), y si el producto no tiene nada cargado ahí,
     * cae a la foto principal real del catálogo de la tienda (producto_imagenes)
     * — así funciona sin que el dueño cargue nada a mano. Para video no hay
     * fallback: solo existe si se cargó en Conocimiento.
     */
    private function materialId(int $productoId, string $tipo): ?string
    {
        $conocimientoId = DB::table('articulo_conocimiento')
            ->where('articulo_id', $productoId)
            ->where('tipo', $tipo)->where('activo', 1)
            ->whereNotNull('archivo')
            ->orderByDesc('prioridad')->orderBy('id')
            ->value('id');

        if ($conocimientoId) {
            return (string) $conocimientoId;
        }

        if ($tipo !== 'imagen') {
            return null;
        }

        $imagenId = DB::table('producto_imagenes')
            ->where('producto_id', $productoId)
            ->orderByDesc('principal')->orderBy('orden')
            ->value('id');

        return $imagenId ? 'img:' . $imagenId : null;
    }
}
