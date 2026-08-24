<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Jobs\RunAiAgent;
use App\Models\AiAgent;
use App\Models\Cliente;
use App\Models\WaAccount;
use App\Models\WaConversation;
use App\Models\WaMessage;
use App\Support\PhoneAr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Webhook del bridge Node.js (Baileys, WhatsApp no oficial). No usa el formato
 * entry[].changes[] de Meta ni su firma HMAC: valida un shared secret propio.
 * A diferencia de Meta, el bridge ya descarga la media el mismo y la manda
 * como multipart, asi que se guarda directo (sin pasar por DownloadWaMedia).
 */
class BaileysWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $token = config('services.whatsapp_baileys.inbound_token');
        if (!$token || !hash_equals($token, (string) $request->header('X-Bridge-Token', ''))) {
            Log::warning('Webhook Baileys con token invalido', ['ip' => $request->ip()]);
            return response('Unauthorized', 401);
        }

        $messageId = $request->input('message_id');
        $jid = $request->input('from');
        if (!$messageId || !$jid) {
            return response()->json(['status' => 'ignored'], 200);
        }

        if (WaMessage::where('wa_message_id', $messageId)->exists()) {
            return response()->json(['status' => 'ok']); // idempotencia: el bridge puede reintentar
        }

        $account = WaAccount::firstOrCreate(
            ['provider' => 'baileys'],
            ['nombre' => 'WhatsApp (no oficial)', 'channel' => 'whatsapp', 'activo' => true]
        );

        // JIDs @lid no traen el numero: usar from_alt (senderPn) que manda el bridge
        $numeroCrudo = explode('@', $jid)[0] ?? '';
        if (str_ends_with($jid, '@lid')) {
            $numeroCrudo = explode('@', (string) $request->input('from_alt', ''))[0] ?? '';
        }
        $phoneE164 = PhoneAr::toE164($numeroCrudo);

        $conversation = WaConversation::firstOrCreate(
            ['wa_account_id' => $account->id, 'external_id' => $jid],
            [
                'channel' => 'whatsapp',
                'phone_e164' => $phoneE164,
                'profile_name' => $request->input('push_name'),
                'status' => 'nueva',
                'mode' => 'bot',
                'ai_agent_id' => AiAgent::where('activo', true)->value('id'),
                'cliente_id' => $phoneE164 ? Cliente::wherePhoneMatches($phoneE164)->value('idcliente') : null,
            ]
        );

        $pushName = $request->input('push_name');
        if ($pushName && $conversation->profile_name !== $pushName) {
            $conversation->profile_name = $pushName;
        }

        // Conversaciones viejas sin numero: completarlo apenas se conozca
        if (!$conversation->phone_e164 && $phoneE164) {
            $conversation->phone_e164 = $phoneE164;
            if (!$conversation->cliente_id) {
                $conversation->cliente_id = Cliente::wherePhoneMatches($phoneE164)->value('idcliente');
            }
        }

        $type = $request->input('type', 'text');
        $body = (string) $request->input('body', '');
        $mediaPath = null;
        $mediaMime = null;

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mediaPath = $file->store('wa-media/' . $conversation->id, 'local');
            $mediaMime = $file->getMimeType();
        }

        $timestamp = $request->filled('timestamp')
            ? Carbon::createFromTimestamp((int) $request->input('timestamp'))
            : now();

        $waMessage = $conversation->messages()->create([
            'wa_message_id' => $messageId,
            'direction' => 'in',
            'type' => $type,
            'body' => $body,
            'media_path' => $mediaPath,
            'media_mime' => $mediaMime,
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
            $conversation->ai_agent_id = $conversation->ai_agent_id ?: AiAgent::where('activo', true)->value('id');
            $conversation->assigned_user_id = null;
        }
        if ($conversation->status === 'cerrada' || $conversation->status === 'esperando_cliente') {
            $conversation->status = $conversation->assigned_user_id ? 'en_atencion' : 'nueva';
        }
        $conversation->save();

        // Conversaciones creadas antes de que existiera un agente quedan sin
        // ai_agent_id: se les asigna el agente activo en el momento.
        if ($conversation->mode === 'bot' && !$conversation->ai_agent_id) {
            $conversation->ai_agent_id = AiAgent::where('activo', true)->value('id');
            $conversation->save();
        }

        if ($conversation->mode === 'bot' && $conversation->ai_agent_id) {
            RunAiAgent::dispatch($waMessage->id);
        }

        return response()->json(['status' => 'ok']);
    }
}
