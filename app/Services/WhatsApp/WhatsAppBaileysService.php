<?php

namespace App\Services\WhatsApp;

use App\Models\WaAccount;
use Illuminate\Support\Facades\Http;

/**
 * Cliente del bridge Node.js (Baileys) que mantiene la sesion de WhatsApp Web.
 * Ver whatsapp-bridge/. El bridge aplica el delay de "escribiendo..." y el
 * rate-limit antes de enviar; este servicio solo le pide el envio por HTTP.
 */
class WhatsAppBaileysService
{
    protected WaAccount $account;

    public function __construct(WaAccount $account)
    {
        $this->account = $account;
    }

    public static function forAccount(WaAccount $account): self
    {
        return new self($account);
    }

    /**
     * @param string $to JID de WhatsApp (external_id de la conversacion), ej. "5493511234567@s.whatsapp.net"
     * @return string|null id del mensaje que devuelve el bridge
     */
    public function sendText(string $to, string $body): ?string
    {
        $response = Http::withToken(config('services.whatsapp_baileys.bridge_token'))
            ->acceptJson()
            ->timeout(20)
            ->post(rtrim(config('services.whatsapp_baileys.bridge_url'), '/') . '/send', [
                'to' => $to,
                'body' => $body,
            ]);

        if ($response->failed()) {
            $error = $response->json('error') ?? $response->body();
            throw new WhatsAppApiException(
                is_string($error) ? $error : ($error['message'] ?? 'Error desconocido del bridge Baileys'),
                (string) $response->status(),
                is_array($error) ? $error : []
            );
        }

        return $response->json('id');
    }
}
