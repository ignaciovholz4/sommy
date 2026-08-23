<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;
use App\Models\WaTag;

/**
 * El bot etiqueta la conversacion segun la intencion del cliente, con un set
 * fijo de etiquetas comerciales (evita que el modelo invente etiquetas nuevas).
 */
class EtiquetarConversacion
{
    public const ETIQUETAS = [
        'quiere comprar'  => '#fd7e14',
        'pidiendo precio' => '#0d6efd',
        'sin stock'       => '#ffc107',
        'reclamo'         => '#dc3545',
        'post-venta'      => '#20c997',
        'mayorista'       => '#6610f2',
    ];

    public static function definition(): array
    {
        return [
            'name' => 'etiquetar_conversacion',
            'description' => 'Etiqueta esta conversación según la intención del cliente, para que el equipo la encuentre filtrando la bandeja. Usala apenas detectes la intención (se puede etiquetar más de una vez con etiquetas distintas). No avises al cliente que etiquetaste.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'etiqueta' => [
                        'type' => 'string',
                        'enum' => array_keys(self::ETIQUETAS),
                        'description' => 'quiere comprar: intención clara de compra · pidiendo precio: consulta precios/presupuesto · sin stock: pidió algo sin stock · reclamo: queja o problema · post-venta: consulta sobre algo ya comprado · mayorista: pregunta por compra al por mayor',
                    ],
                ],
                'required' => ['etiqueta'],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $nombre = $args['etiqueta'] ?? '';
        if (!isset(self::ETIQUETAS[$nombre])) {
            return ['error' => 'Etiqueta inválida. Usá una de: ' . implode(', ', array_keys(self::ETIQUETAS))];
        }

        $tag = WaTag::firstOrCreate(['nombre' => $nombre], ['color' => self::ETIQUETAS[$nombre]]);
        $conversation->tags()->syncWithoutDetaching([$tag->id]);

        return ['resultado' => 'Conversación etiquetada como "' . $nombre . '". Seguí la charla normalmente.'];
    }
}
