<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;
use App\Models\WaOrderDraft;
use App\Support\Precio;
use Illuminate\Support\Facades\DB;

class Cotizar
{
    public static function definition(): array
    {
        return [
            'name' => 'cotizar',
            'description' => 'Arma o actualiza la cotización (borrador de pedido) de esta conversación con los productos y cantidades que el cliente quiere. Los precios se toman del sistema, no los pases vos. Si entre los items está el producto ancla de un combo junto con alguno de sus relacionados o regalos (los que te devolvió buscar_productos en "combo"), el sistema aplica solo el descuento y el regalo a $0 automáticamente. Devuelve el detalle y el total para presentarle al cliente.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'items' => [
                        'type' => 'array',
                        'description' => 'Productos a cotizar (reemplaza la cotización anterior)',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'producto_id' => ['type' => 'integer'],
                                'combinacion_id' => ['type' => 'integer', 'description' => 'OBLIGATORIO si el producto tiene variantes: la medida/color exacta que eligió el cliente (el precio sale de ahí)'],
                                'cantidad' => ['type' => 'integer', 'minimum' => 1],
                            ],
                            'required' => ['producto_id', 'cantidad'],
                        ],
                    ],
                ],
                'required' => ['items'],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $itemsIn = $args['items'] ?? [];
        if (empty($itemsIn)) {
            return ['error' => 'La cotización necesita al menos un producto'];
        }

        $items = [];
        foreach ($itemsIn as $item) {
            $producto = DB::table('productos')
                ->where('idarticulo', (int) ($item['producto_id'] ?? 0))
                ->where('estado', 'Activo')
                ->first();
            if (!$producto) {
                return ['error' => 'Producto ' . ($item['producto_id'] ?? '?') . ' inexistente. Volvé a buscarlo con buscar_productos.'];
            }
            $cantidad = max(1, (int) ($item['cantidad'] ?? 1));

            // Con variante: el precio y la descripcion salen de la combinacion elegida
            $combinacionId = (int) ($item['combinacion_id'] ?? 0);
            $combinacion = null;
            if ($combinacionId) {
                $combinacion = DB::table('producto_combinaciones')
                    ->where('idcombinacion', $combinacionId)
                    ->where('producto_id', $producto->idarticulo)
                    ->first();
                if (!$combinacion) {
                    return ['error' => 'La variante ' . $combinacionId . ' no corresponde al producto ' . $producto->nombre . '. Revisá las variantes con buscar_productos.'];
                }
            } else {
                $tieneVariantes = DB::table('producto_combinaciones')->where('producto_id', $producto->idarticulo)->exists();
                if ($tieneVariantes) {
                    return ['error' => $producto->nombre . ' tiene variantes (medidas/colores): preguntale al cliente cuál quiere y cotizá con su combinacion_id — el precio depende de la variante.'];
                }
            }

            $items[] = [
                'producto_id' => $producto->idarticulo,
                'combinacion_id' => $combinacion->idcombinacion ?? null,
                'cantidad' => $cantidad,
                'precio_unitario' => (float) ($combinacion->pventa_variante ?? $producto->pventa_con_iva),
                'descripcion' => $producto->nombre . ($combinacion ? ' — ' . $combinacion->combinacion : ''),
            ];
        }

        $items = $this->aplicarCombos($items);

        // Un borrador activo por conversacion: se pisa con la nueva cotizacion
        $draft = WaOrderDraft::firstOrNew([
            'conversation_id' => $conversation->id,
            'status' => 'borrador',
        ]);
        $draft->fill([
            'cliente_id' => $conversation->cliente_id,
            'ai_agent_id' => $agent->id,
            'items' => $items,
        ]);
        $draft->recalcularTotales();
        $draft->save();

        return [
            'cotizacion_id' => $draft->id,
            'items' => array_map(fn ($i) => [
                'producto' => $i['descripcion'],
                'cantidad' => $i['cantidad'],
                'precio_unitario' => $i['precio_unitario'],
                'subtotal' => round($i['cantidad'] * $i['precio_unitario'], 2),
            ], $items),
            'total' => $draft->total,
            'nota' => 'VERIFICÁ: estos items y este total son los REALES que va a llevar el pedido. El resumen que le mandes al cliente se copia EXACTAMENTE de acá (producto, medida, precio, total) — nunca redondees ni recalcules. Si algo no coincide con lo que venías hablando (producto o medida distintos), corregí la cotización ANTES de resumir. Si el cliente acepta, pedile dirección de entrega y usá crear_pedido.',
        ];
    }

    /**
     * Si entre los items cotizados está el producto ancla de un combo activo
     * (combo_descuento_pct > 0) junto con alguno de sus relacionados o
     * regalos configurados en almacen/combos, aplica automáticamente el
     * descuento o el precio $0 del regalo — nunca a partir de lo que diga el
     * LLM, siempre recalculado acá con los datos reales del combo.
     */
    private function aplicarCombos(array $items): array
    {
        $productoIds = collect($items)->pluck('producto_id')->unique();

        $anchors = DB::table('productos')
            ->whereIn('idarticulo', $productoIds)
            ->where('combo_descuento_pct', '>', 0)
            ->pluck('combo_descuento_pct', 'idarticulo');

        if ($anchors->isEmpty()) {
            return $items;
        }

        foreach ($anchors as $anchorId => $descuentoPct) {
            $regalos = DB::table('producto_regalos')
                ->where('idarticulo', $anchorId)
                ->pluck('cantidad', 'regalo_id');

            $relacionadoIds = DB::table('producto_relacionados')
                ->where('idarticulo', $anchorId)
                ->whereNotIn('relacionado_id', $regalos->keys())
                ->pluck('relacionado_id');

            foreach ($items as &$item) {
                if ($item['producto_id'] === $anchorId) {
                    continue;
                }

                if ($regalos->has($item['producto_id']) && $item['cantidad'] <= $regalos->get($item['producto_id'])) {
                    $item['precio_unitario'] = 0.0;
                    $item['descripcion'] .= ' — de regalo por combo';
                } elseif ($relacionadoIds->contains($item['producto_id'])) {
                    $item['precio_unitario'] = Precio::redondear($item['precio_unitario'] * (1 - $descuentoPct / 100));
                    $item['descripcion'] .= ' — con descuento de combo';
                }
            }
            unset($item);
        }

        return $items;
    }
}
