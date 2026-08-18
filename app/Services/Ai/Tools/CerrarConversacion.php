<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;

class CerrarConversacion
{
    public static function definition(): array
    {
        return [
            'name' => 'cerrar_conversacion',
            'description' => 'Marca la conversación como cerrada (consulta resuelta). Usala SOLO cuando el tema quedó resuelto y el cliente se despidió o indicó que no necesita nada más. Podés despedirte en el mismo mensaje. Si el cliente vuelve a escribir, la conversación se reabre sola.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'resumen' => ['type' => 'string', 'description' => 'Resumen breve de cómo quedó resuelta la consulta'],
                ],
                'required' => [],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $conversation->update(['status' => 'cerrada']);

        return ['ok' => true, 'mensaje' => 'Conversación marcada como cerrada. Podés enviar una despedida breve.'];
    }
}
