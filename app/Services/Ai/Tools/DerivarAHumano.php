<?php

namespace App\Services\Ai\Tools;

class DerivarAHumano
{
    public static function definition(): array
    {
        return [
            'name' => 'derivar_a_humano',
            'description' => 'Deriva la conversación a un vendedor humano. Usala cuando el cliente lo pida, esté molesto, plantee reclamos/cambios/facturación, o cuando no puedas resolver algo con seguridad. Después de usarla no vas a responder más en esta conversación.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'motivo' => ['type' => 'string', 'description' => 'Motivo breve de la derivación, para el vendedor'],
                ],
                'required' => ['motivo'],
            ],
        ];
    }

    // La ejecucion real la maneja AiAgentService (corta el loop y cambia el modo).
}
