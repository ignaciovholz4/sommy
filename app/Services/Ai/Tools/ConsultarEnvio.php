<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;
use App\Models\ZonaEnvio;

/**
 * Zonas de envio activas con su costo, para que el bot pueda responder
 * "cuanto sale el envio" con datos reales (no hay matching automatico de
 * localidad de texto -> zona: el modelo coteja el nombre contra lo que
 * dijo el cliente).
 */
class ConsultarEnvio
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_envio',
            'description' => 'Lista las zonas de envío disponibles con su costo real. Usala cuando el cliente pregunte cuánto sale el envío o a qué zonas llegan. Compará el nombre de zona con la localidad que mencionó el cliente; si no está clara la coincidencia, pedile la localidad para confirmarle el costo exacto.',
            'parameters' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $zonas = ZonaEnvio::activas()->get(['nombre', 'costo']);

        if ($zonas->isEmpty()) {
            return ['resultado' => 'No hay zonas de envío cargadas todavía. Derivá a un vendedor para confirmar el costo del envío.'];
        }

        return [
            'zonas' => $zonas->map(fn (ZonaEnvio $z) => [
                'nombre' => $z->nombre,
                'costo' => (float) $z->costo,
            ])->all(),
        ];
    }
}
