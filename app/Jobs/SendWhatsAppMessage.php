<?php

namespace App\Jobs;

use App\Models\WaMessage;
use App\Services\WhatsApp\WhatsAppApiException;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Envia un wa_message ya persistido (status=pending) a la Cloud API.
 * El registro se crea antes (en el controlador de la bandeja o en el agente IA)
 * para que aparezca al instante en la UI; este job solo lo despacha a Meta.
 */
class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 15;

    public int $waMessageId;

    /** Payload extra para plantillas: {name, language, params[]} */
    public ?array $template;

    public function __construct(int $waMessageId, ?array $template = null)
    {
        $this->waMessageId = $waMessageId;
        $this->template = $template;
    }

    public function handle(): void
    {
        $message = WaMessage::with('conversation.account')->find($this->waMessageId);
        if (!$message || $message->status !== 'pending') {
            return;
        }

        $conversation = $message->conversation;

        try {
            // Messenger / Instagram via Messenger Platform
            if ($conversation->channel !== 'whatsapp') {
                $service = \App\Services\WhatsApp\MessengerService::forAccount($conversation->account);

                if (in_array($message->type, ['image', 'video', 'audio', 'document']) && $message->media_path) {
                    $mid = $service->sendMedia($conversation->external_id, $message->type, $message->media_path, $message->body ?: null);
                } else {
                    $mid = $service->sendText($conversation->external_id, (string) $message->body);
                }

                $message->update(['wa_message_id' => $mid ?: null, 'status' => 'sent']);
                return;
            }

            // WhatsApp no oficial (Baileys): el "to" es el JID guardado en external_id,
            // no el phone_e164 (formato distinto al wa_id que usa la Cloud API de Meta).
            if ($conversation->account->provider === 'baileys') {
                $service = \App\Services\WhatsApp\WhatsAppBaileysService::forAccount($conversation->account);

                // Mensajes con adjunto (material de producto que manda el bot):
                // media_path guarda la URL publica del archivo
                if (in_array($message->type, ['image', 'video', 'audio', 'document']) && $message->media_path) {
                    $mid = $service->sendMedia(
                        $conversation->external_id,
                        $message->type,
                        $message->media_path,
                        $message->body ?: null,
                        $message->payload['filename'] ?? null,
                        $message->media_mime
                    );
                } else {
                    $mid = $service->sendText($conversation->external_id, (string) $message->body);
                }

                $message->update(['wa_message_id' => $mid ?: null, 'status' => 'sent']);
                return;
            }

            $service = WhatsAppService::forAccount($conversation->account);

            if (in_array($message->type, ['image', 'video', 'audio', 'document']) && $message->media_path) {
                $payload = $service->buildMediaPayload(
                    $conversation->phone_e164,
                    $message->type,
                    $message->media_path,
                    $message->body ?: null,
                    $message->payload['filename'] ?? null
                );
            } elseif ($this->template) {
                $payload = $service->buildTemplatePayload(
                    $conversation->phone_e164,
                    $this->template['name'],
                    $this->template['language'] ?? 'es_AR',
                    $this->template['params'] ?? []
                );
            } else {
                $payload = $service->buildTextPayload($conversation->phone_e164, (string) $message->body);
            }

            $response = $service->send($payload);

            $message->update([
                'wa_message_id' => $response['messages'][0]['id'] ?? null,
                'status' => 'sent',
            ]);
        } catch (WhatsAppApiException $e) {
            Log::warning('Fallo envio WhatsApp', [
                'wa_message' => $message->id,
                'code' => $e->errorCode,
                'error' => $e->getMessage(),
            ]);

            // Errores definitivos (ventana vencida, numero invalido): no reintentar
            $message->update([
                'status' => 'failed',
                'error_code' => $e->errorCode,
                'error_detail' => $e->getMessage(),
            ]);
        }
    }
}
