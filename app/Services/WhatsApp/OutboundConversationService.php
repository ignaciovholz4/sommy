<?php

namespace App\Services\WhatsApp;

use App\Models\Cliente;
use App\Models\WaConversation;
use App\Support\PhoneAr;

/**
 * Origina una WaConversation para mandarle un mensaje a un cliente que
 * todavia no le escribio al negocio (hasta ahora, TODA WaConversation nacia
 * de un mensaje entrante via WebhookProcessor). Se usa para cobranzas:
 * la conversacion queda en modo "humano" siempre, nunca "bot", porque no hay
 * un mensaje entrante que dispare al agente de ventas.
 */
class OutboundConversationService
{
    public function forCliente(Cliente $cliente): ?WaConversation
    {
        $phone = PhoneAr::toE164($cliente->telefono);
        if (!$phone) {
            return null;
        }

        $account = WhatsAppService::defaultAccount();
        if (!$account) {
            return null;
        }

        return WaConversation::firstOrCreate(
            ['wa_account_id' => $account->id, 'external_id' => $phone],
            [
                'channel' => 'whatsapp',
                'phone_e164' => $phone,
                'profile_name' => trim($cliente->nombre . ' ' . ($cliente->paterno ?? '')) ?: null,
                'status' => 'nueva',
                'mode' => 'humano',
                'cliente_id' => $cliente->idcliente,
            ]
        );
    }
}
