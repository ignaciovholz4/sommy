<?php

namespace App\Services\Ai\Tools;

use App\Jobs\SendWhatsAppMessage;
use App\Models\AiAgent;
use App\Models\ArticuloConocimiento;
use App\Models\ProductoImagen;
use App\Models\WaConversation;
use Illuminate\Support\Str;

/**
 * Envia al cliente un archivo (foto, video, audio o documento) como adjunto
 * REAL — WhatsApp (Baileys o Meta Cloud API), Messenger o Instagram Direct.
 * El material_id puede venir de info_producto (base de conocimiento) o de
 * buscar_productos con el prefijo "img:" (foto real del catálogo).
 */
class EnviarMaterial
{
    public static function definition(): array
    {
        return [
            'name' => 'enviar_material',
            'description' => 'Envía al cliente una foto, video, audio o documento como adjunto real (no un link). Usá el material_id que devuelve buscar_productos (foto_material_id, video_material_id) o info_producto. Al presentar un producto mandá siempre su foto, y su video también si tiene. Podés agregar un mensaje corto que acompañe el archivo.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'material_id' => [
                        'type' => 'string',
                        'description' => 'El material_id/foto_material_id devuelto por buscar_productos o info_producto',
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
        $materialId = trim((string) ($args['material_id'] ?? ''));
        $mensaje = trim((string) ($args['mensaje'] ?? ''));

        if (str_starts_with($materialId, 'img:')) {
            $imagen = ProductoImagen::find((int) substr($materialId, 4));
            if (!$imagen || !$imagen->path) {
                return ['error' => 'No existe esa foto de catálogo.'];
            }

            $this->enviar($conversation, $agent, 'image', $mensaje, asset($imagen->path), null, 'Foto del producto');

            return ['resultado' => 'Foto enviada. No repitas el contenido de la imagen en texto; seguí la conversación normalmente.'];
        }

        $item = ArticuloConocimiento::where('activo', true)->find((int) $materialId);

        if (!$item || !$item->archivo) {
            return ['error' => 'No existe material con ese id o no tiene archivo.'];
        }

        // Tipo de mensaje segun el tipo de conocimiento
        $tipo = match ($item->tipo) {
            'imagen' => 'image',
            'video'  => 'video',
            'audio'  => 'audio',
            default  => 'document',
        };

        $this->enviar($conversation, $agent, $tipo, $mensaje, $item->archivo_url, $item->mime, $item->titulo, $item->id);

        return [
            'resultado' => 'Enviado: ' . $item->titulo . ' (' . (ArticuloConocimiento::TIPOS[$item->tipo] ?? $item->tipo) . '). No repitas el contenido del archivo en texto; seguí la conversación normalmente.',
        ];
    }

    private function enviar(WaConversation $conversation, AiAgent $agent, string $tipo, string $mensaje, string $url, ?string $mime, string $titulo, ?int $conocimientoId = null): void
    {
        $message = $conversation->messages()->create([
            'direction'        => 'out',
            'type'             => $tipo,
            'body'             => $mensaje ?: null,
            'media_path'       => $url, // URL publica: cada canal la descarga y la adjunta
            'media_mime'       => $mime,
            'payload'          => array_filter(['filename' => $titulo, 'conocimiento_id' => $conocimientoId]),
            'status'           => 'pending',
            'sent_by_agent_id' => $agent->id,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => Str::limit('📎 ' . $titulo . ($mensaje ? ' — ' . $mensaje : ''), 120),
        ]);

        SendWhatsAppMessage::dispatch($message->id);
    }
}
