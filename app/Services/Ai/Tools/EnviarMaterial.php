<?php

namespace App\Services\Ai\Tools;

use App\Jobs\SendWhatsAppMessage;
use App\Models\AiAgent;
use App\Models\ArticuloConocimiento;
use App\Models\WaConversation;
use Illuminate\Support\Str;

/**
 * Envia al cliente un archivo de la base de conocimiento del producto
 * (foto, video, audio o documento) como adjunto REAL de WhatsApp, no un link.
 * Solo funciona en cuentas Baileys; en cuentas Meta devuelve el link para
 * que el bot lo comparta como texto.
 */
class EnviarMaterial
{
    public static function definition(): array
    {
        return [
            'name' => 'enviar_material',
            'description' => 'Envía al cliente una foto, video, audio o documento de la ficha del producto como adjunto de WhatsApp. Usá el material_id que devuelve info_producto. Podés agregar un mensaje corto que acompañe el archivo.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'material_id' => [
                        'type' => 'integer',
                        'description' => 'El material_id devuelto por info_producto',
                    ],
                    'mensaje' => [
                        'type' => 'string',
                        'description' => 'Texto corto opcional que acompaña el archivo (ej: "Acá te muestro el colchón que te decía")',
                    ],
                ],
                'required' => ['material_id'],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $item = ArticuloConocimiento::where('activo', true)->find((int) ($args['material_id'] ?? 0));

        if (!$item || !$item->archivo) {
            return ['error' => 'No existe material con ese id o no tiene archivo.'];
        }

        // En cuentas que no son Baileys (Meta Cloud API) no mandamos adjunto: va el link
        if ($conversation->channel !== 'whatsapp' || $conversation->account->provider !== 'baileys') {
            return [
                'resultado' => 'En este canal no se pueden mandar adjuntos automáticos. Compartile este link al cliente: ' . $item->archivo_url,
            ];
        }

        // Tipo de mensaje segun el tipo de conocimiento
        $tipo = match ($item->tipo) {
            'imagen' => 'image',
            'video'  => 'video',
            'audio'  => 'audio',
            default  => 'document',
        };

        $mensaje = trim((string) ($args['mensaje'] ?? ''));

        $message = $conversation->messages()->create([
            'direction'        => 'out',
            'type'             => $tipo,
            'body'             => $mensaje ?: null,
            'media_path'       => $item->archivo_url, // URL publica: el bridge la descarga y la adjunta
            'media_mime'       => $item->mime,
            'payload'          => ['filename' => $item->titulo, 'conocimiento_id' => $item->id],
            'status'           => 'pending',
            'sent_by_agent_id' => $agent->id,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => Str::limit('📎 ' . $item->titulo . ($mensaje ? ' — ' . $mensaje : ''), 120),
        ]);

        SendWhatsAppMessage::dispatch($message->id);

        return [
            'resultado' => 'Enviado: ' . $item->titulo . ' (' . (ArticuloConocimiento::TIPOS[$item->tipo] ?? $item->tipo) . '). No repitas el contenido del archivo en texto; seguí la conversación normalmente.',
        ];
    }
}
