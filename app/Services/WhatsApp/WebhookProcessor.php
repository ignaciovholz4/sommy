<?php

namespace App\Services\WhatsApp;

use App\Jobs\DownloadWaMedia;
use App\Jobs\RunAiAgent;
use App\Models\AiAgent;
use App\Models\Cliente;
use App\Models\WaAccount;
use App\Models\WaConversation;
use App\Models\WaMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Procesa el payload crudo del webhook de Meta (corre dentro del job
 * ProcessWhatsAppWebhook, nunca en el request del webhook).
 */
class WebhookProcessor
{
    public function process(array $payload): void
    {
        $object = $payload['object'] ?? '';

        // Messenger (paginas de Facebook) e Instagram Direct
        if (in_array($object, ['page', 'instagram'], true)) {
            $channel = $object === 'page' ? 'messenger' : 'instagram';
            foreach ($payload['entry'] ?? [] as $entry) {
                foreach ($entry['messaging'] ?? [] as $event) {
                    $this->handleMessagingEvent($channel, (string) $entry['id'], $event);
                }
            }
            return;
        }

        // WhatsApp Business
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'messages') {
                    continue;
                }
                $value = $change['value'] ?? [];
                $account = $this->resolveAccount($value);
                if (!$account) {
                    Log::warning('Webhook WhatsApp sin cuenta reconocida', ['value' => $value]);
                    continue;
                }

                foreach ($value['messages'] ?? [] as $message) {
                    $this->handleInbound($account, $value, $message);
                }

                foreach ($value['statuses'] ?? [] as $status) {
                    $this->handleStatus($status);
                }
            }
        }
    }

    /**
     * Evento de la Messenger Platform (Facebook Messenger o Instagram Direct).
     * $entryId: page_id (messenger) o ig_account_id (instagram).
     */
    protected function handleMessagingEvent(string $channel, string $entryId, array $event): void
    {
        $message = $event['message'] ?? null;
        if (!$message || !empty($message['is_echo'])) {
            return; // solo mensajes entrantes; los echo son nuestros propios envios
        }

        $mid = $message['mid'] ?? null;
        $senderId = $event['sender']['id'] ?? null;
        if (!$mid || !$senderId || WaMessage::where('wa_message_id', $mid)->exists()) {
            return;
        }

        $account = WaAccount::firstOrCreate(
            $channel === 'messenger' ? ['channel' => 'messenger', 'page_id' => $entryId]
                                     : ['channel' => 'instagram', 'ig_account_id' => $entryId],
            [
                'nombre' => $channel === 'messenger' ? 'Página de Facebook' : 'Instagram',
                'page_id' => $channel === 'instagram' ? config('services.whatsapp.page_id') : $entryId,
                'activo' => true,
            ]
        );

        $conversation = WaConversation::firstOrCreate(
            ['wa_account_id' => $account->id, 'external_id' => $senderId],
            [
                'channel' => $channel,
                'status' => 'nueva',
                'mode' => 'bot',
                'ai_agent_id' => AiAgent::where('activo', true)->value('id'),
            ]
        );

        if (!$conversation->profile_name) {
            $name = MessengerService::forAccount($account)->fetchProfileName($senderId);
            if ($name) {
                $conversation->profile_name = $name;
            }
        }

        $attachment = $message['attachments'][0] ?? null;
        $type = $attachment ? ($attachment['type'] ?? 'file') : 'text';
        $body = $message['text'] ?? '';
        $mediaUrl = $attachment['payload']['url'] ?? null;

        $timestamp = isset($event['timestamp'])
            ? Carbon::createFromTimestampMs((int) $event['timestamp'])
            : now();

        $waMessage = $conversation->messages()->create([
            'wa_message_id' => $mid,
            'direction' => 'in',
            'type' => $type === 'file' ? 'document' : $type,
            'body' => $body,
            'media_id' => $mediaUrl, // URL directa del CDN de Meta (DownloadWaMedia la baja sin token)
            'payload' => $event,
            'wa_timestamp' => $timestamp,
        ]);

        $conversation->fill([
            'last_inbound_at' => $timestamp,
            'last_message_at' => $timestamp,
            'last_message_preview' => Str::limit($body !== '' ? $body : "[{$type}]", 120),
            'unread_count' => $conversation->unread_count + 1,
        ]);
        // Conversación cerrada que revive: arranca de cero con el bot
        if ($conversation->status === 'cerrada') {
            $conversation->mode = 'bot';
            $conversation->ai_agent_id = $conversation->ai_agent_id ?: \App\Models\AiAgent::where('activo', true)->value('id');
            $conversation->assigned_user_id = null;
        }
        if ($conversation->status === 'cerrada' || $conversation->status === 'esperando_cliente') {
            $conversation->status = $conversation->assigned_user_id ? 'en_atencion' : 'nueva';
        }
        $conversation->save();

        if ($mediaUrl) {
            DownloadWaMedia::dispatch($waMessage->id);
        }

        // Conversaciones creadas antes de que existiera un agente quedan sin
        // ai_agent_id: se les asigna el agente activo en el momento.
        if ($conversation->mode === 'bot' && !$conversation->ai_agent_id) {
            $conversation->ai_agent_id = \App\Models\AiAgent::where('activo', true)->value('id');
            $conversation->save();
        }

        if ($conversation->mode === 'bot' && $conversation->ai_agent_id) {
            RunAiAgent::dispatch($waMessage->id);
        }
    }

    protected function resolveAccount(array $value): ?WaAccount
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
        if (!$phoneNumberId) {
            return null;
        }

        return WaAccount::firstOrCreate(
            ['phone_number_id' => $phoneNumberId],
            [
                'nombre' => 'Principal',
                'display_phone' => $value['metadata']['display_phone_number'] ?? null,
                'waba_id' => config('services.whatsapp.waba_id'),
                'verify_token' => config('services.whatsapp.verify_token'),
                'activo' => true,
            ]
        );
    }

    protected function handleInbound(WaAccount $account, array $value, array $message): void
    {
        $waMessageId = $message['id'] ?? null;
        if (!$waMessageId || WaMessage::where('wa_message_id', $waMessageId)->exists()) {
            return; // idempotencia: Meta reintenta webhooks
        }

        $phone = $message['from'] ?? null; // formato wa_id: 549XXXXXXXXXX
        if (!$phone) {
            return;
        }

        $profileName = collect($value['contacts'] ?? [])
            ->firstWhere('wa_id', $phone)['profile']['name'] ?? null;

        $conversation = WaConversation::firstOrCreate(
            ['wa_account_id' => $account->id, 'external_id' => $phone],
            [
                'channel' => 'whatsapp',
                'phone_e164' => $phone,
                'profile_name' => $profileName,
                'status' => 'nueva',
                'mode' => 'bot',
                'ai_agent_id' => AiAgent::where('activo', true)->value('id'),
                'cliente_id' => Cliente::wherePhoneMatches($phone)->value('idcliente'),
            ]
        );

        if ($profileName && $conversation->profile_name !== $profileName) {
            $conversation->profile_name = $profileName;
        }

        $type = $message['type'] ?? 'text';
        $body = match ($type) {
            'text' => $message['text']['body'] ?? '',
            'button' => $message['button']['text'] ?? '',
            'interactive' => $message['interactive']['button_reply']['title']
                ?? $message['interactive']['list_reply']['title'] ?? '',
            'location' => trim(($message['location']['name'] ?? '') . ' '
                . ($message['location']['latitude'] ?? '') . ','
                . ($message['location']['longitude'] ?? '')),
            default => $message[$type]['caption'] ?? '',
        };

        $mediaId = $message[$type]['id'] ?? null;
        $timestamp = isset($message['timestamp'])
            ? Carbon::createFromTimestamp((int) $message['timestamp'])
            : now();

        $waMessage = $conversation->messages()->create([
            'wa_message_id' => $waMessageId,
            'direction' => 'in',
            'type' => $type,
            'body' => $body,
            'media_id' => $mediaId,
            'media_mime' => $message[$type]['mime_type'] ?? null,
            'payload' => $message,
            'wa_timestamp' => $timestamp,
        ]);

        $conversation->fill([
            'last_inbound_at' => $timestamp,
            'last_message_at' => $timestamp,
            'last_message_preview' => Str::limit($body !== '' ? $body : "[{$type}]", 120),
            'unread_count' => $conversation->unread_count + 1,
        ]);
        // Conversación cerrada que revive: arranca de cero con el bot
        if ($conversation->status === 'cerrada') {
            $conversation->mode = 'bot';
            $conversation->ai_agent_id = $conversation->ai_agent_id ?: \App\Models\AiAgent::where('activo', true)->value('id');
            $conversation->assigned_user_id = null;
        }
        if ($conversation->status === 'cerrada' || $conversation->status === 'esperando_cliente') {
            $conversation->status = $conversation->assigned_user_id ? 'en_atencion' : 'nueva';
        }
        $conversation->save();

        if ($mediaId) {
            DownloadWaMedia::dispatch($waMessage->id);
        }

        if ($conversation->mode === 'bot' && !$conversation->ai_agent_id) {
            $conversation->ai_agent_id = \App\Models\AiAgent::where('activo', true)->value('id');
            $conversation->save();
        }

        if ($conversation->mode === 'bot' && $conversation->ai_agent_id) {
            RunAiAgent::dispatch($waMessage->id);
        }
    }

    protected function handleStatus(array $status): void
    {
        $waMessageId = $status['id'] ?? null;
        $newStatus = $status['status'] ?? null; // sent | delivered | read | failed
        if (!$waMessageId || !$newStatus) {
            return;
        }

        $message = WaMessage::where('wa_message_id', $waMessageId)->first();
        if (!$message) {
            return;
        }

        // No retroceder estados (Meta puede mandar delivered despues de read)
        $order = ['pending' => 0, 'sent' => 1, 'delivered' => 2, 'read' => 3, 'failed' => 4];
        if (($order[$newStatus] ?? 0) <= ($order[$message->status] ?? 0) && $newStatus !== 'failed') {
            return;
        }

        $message->status = $newStatus;
        if ($newStatus === 'failed') {
            $error = $status['errors'][0] ?? [];
            $message->error_code = (string) ($error['code'] ?? '');
            $message->error_detail = $error['title'] ?? ($error['message'] ?? null);
        }
        $message->save();
    }
}
