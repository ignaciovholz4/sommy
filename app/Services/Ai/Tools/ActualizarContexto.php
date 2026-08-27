<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;

/**
 * "Memoria" de la conversacion: lo que ya sabemos del cliente en terminos de
 * venta (medida, tipo de colchon, producto que le interesa, etapa comercial).
 * Es silenciosa — el bot la llama para no volver a preguntar lo mismo, nunca
 * le avisa al cliente. Se guarda en wa_conversations.contexto_venta.
 */
class ActualizarContexto
{
    public const ETAPAS = ['explorando', 'interesado', 'intencion_compra', 'cerrado'];

    public static function definition(): array
    {
        return [
            'name' => 'actualizar_contexto',
            'description' => 'Guarda internamente lo que ya sabés de este cliente (medida, tipo de colchón, producto que le interesó, etapa comercial) para no volver a preguntarlo. Llamala cada vez que el cliente te cuente un dato nuevo de estos, o cuando cambie de etapa (por ejemplo, cuando diga algo como "lo quiero" o "cómo hago para comprarlo" pasá etapa a intencion_compra). Es silenciosa: nunca le menciones al cliente que estás guardando esto.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'medida' => ['type' => 'string', 'description' => 'Medida del colchón que busca, ej: "1,40x1,90"'],
                    'tipo_colchon' => ['type' => 'string', 'description' => 'Ej: "espuma" o "resortes"'],
                    'producto_interes_id' => ['type' => 'integer', 'description' => 'producto_id del producto que más le interesó hasta ahora'],
                    'producto_interes_nombre' => ['type' => 'string'],
                    'etapa' => ['type' => 'string', 'enum' => self::ETAPAS, 'description' => 'explorando (recién arranca) / interesado (ya vio opciones) / intencion_compra (dijo que lo quiere) / cerrado (ya hizo el pedido)'],
                ],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $nuevo = array_filter([
            'medida' => $args['medida'] ?? null,
            'tipo_colchon' => $args['tipo_colchon'] ?? null,
            'producto_interes_id' => $args['producto_interes_id'] ?? null,
            'producto_interes_nombre' => $args['producto_interes_nombre'] ?? null,
            'etapa' => in_array($args['etapa'] ?? null, self::ETAPAS, true) ? $args['etapa'] : null,
        ], fn ($v) => $v !== null && $v !== '');

        if (empty($nuevo)) {
            return ['error' => 'No pasaste ningún dato para guardar.'];
        }

        $conversation->contexto_venta = array_merge($conversation->contexto_venta ?? [], $nuevo);
        $conversation->save();

        return ['resultado' => 'Contexto guardado. Seguí la conversación normalmente, sin mencionar esto.'];
    }
}
