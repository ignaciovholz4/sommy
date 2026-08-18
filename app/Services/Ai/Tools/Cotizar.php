<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;
use App\Models\WaOrderDraft;
use Illuminate\Support\Facades\DB;

class Cotizar
{
    public static function definition(): array
    {
        return [
            'name' => 'cotizar',
            'description' => 'Arma o actualiza la cotización (borrador de pedido) de esta conversación con los productos y cantidades que el cliente quiere. Los precios se toman del sistema, no los pases vos. Devuelve el detalle y el total para presentarle al cliente.',
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
            $items[] = [
                'producto_id' => $producto->idarticulo,
                'cantidad' => $cantidad,
                'precio_unitario' => (float) $producto->pventa_con_iva,
                'descripcion' => $producto->nombre,
            ];
        }

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
            'nota' => 'Presentale este total al cliente. Si acepta, pedile dirección de entrega y usá crear_pedido.',
        ];
    }
}
