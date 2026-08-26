<?php

namespace App\Services\WhatsApp;

use App\Models\WaAccount;
use Illuminate\Support\Facades\Http;

/**
 * Envio de mensajes por Messenger (paginas de Facebook) e Instagram Direct,
 * via la Messenger Platform de Meta (POST /{page_id}/messages con token de pagina).
 */
class MessengerService
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

    protected function token(): ?string
    {
        // Instagram con la "API de Instagram" nueva usa su propio token de cuenta;
        // Messenger sigue usando el token de la página de Facebook.
        if ($this->usaApiInstagram()) {
            return config('services.whatsapp.ig_access_token');
        }

        return $this->account->token ?: config('services.whatsapp.page_token');
    }

    /** true cuando el canal es Instagram y hay token propio de IG configurado. */
    protected function usaApiInstagram(): bool
    {
        return $this->account->channel === 'instagram'
            && (bool) config('services.whatsapp.ig_access_token');
    }

    protected function baseUrl(): string
    {
        $version = config('services.whatsapp.graph_version', 'v21.0');

        // La API de Instagram vive en su propio host
        return $this->usaApiInstagram()
            ? "https://graph.instagram.com/{$version}"
            : "https://graph.facebook.com/{$version}";
    }

    /**
     * Envia texto a un PSID (Messenger) o IGSID (Instagram).
     * Devuelve el message_id de Meta.
     */
    public function sendText(string $recipientId, string $text): string
    {
        // API de Instagram: se publica sobre "me" (la cuenta dueña del token);
        // Messenger Platform: sobre el page_id con token de página.
        $emisor = $this->usaApiInstagram() ? 'me' : $this->account->page_id;

        $response = Http::withToken($this->token())
            ->acceptJson()
            ->post($this->baseUrl() . '/' . $emisor . '/messages', [
                'recipient' => ['id' => $recipientId],
                'messaging_type' => 'RESPONSE',
                'message' => ['text' => $text],
            ]);

        if ($response->failed()) {
            $error = $response->json('error') ?? ['message' => $response->body()];
            throw new WhatsAppApiException(
                $error['message'] ?? 'Error desconocido de Meta',
                (string) ($error['code'] ?? $response->status()),
                $error
            );
        }

        return (string) $response->json('message_id');
    }

    /**
     * Envia un adjunto (foto/video/audio) por URL publica. La Send API de Meta
     * no permite adjunto + texto en un mismo mensaje: si hay caption, se manda
     * como un sendText() aparte justo despues.
     * Devuelve el message_id del adjunto.
     */
    public function sendMedia(string $recipientId, string $type, string $url, ?string $caption = null): string
    {
        $emisor = $this->usaApiInstagram() ? 'me' : $this->account->page_id;

        // La Send API de Messenger/Instagram usa "file" en vez de "document"
        $tipoAdjunto = $type === 'document' ? 'file' : $type;

        $response = Http::withToken($this->token())
            ->acceptJson()
            ->post($this->baseUrl() . '/' . $emisor . '/messages', [
                'recipient' => ['id' => $recipientId],
                'messaging_type' => 'RESPONSE',
                'message' => [
                    'attachment' => [
                        'type' => $tipoAdjunto,
                        'payload' => ['url' => $url],
                    ],
                ],
            ]);

        if ($response->failed()) {
            $error = $response->json('error') ?? ['message' => $response->body()];
            throw new WhatsAppApiException(
                $error['message'] ?? 'Error desconocido de Meta',
                (string) ($error['code'] ?? $response->status()),
                $error
            );
        }

        $messageId = (string) $response->json('message_id');

        if ($caption) {
            $this->sendText($recipientId, $caption);
        }

        return $messageId;
    }

    /**
     * Nombre visible del contacto (best effort; puede fallar por permisos).
     */
    public function fetchProfileName(string $userId): ?string
    {
        try {
            $fields = $this->account->channel === 'instagram' ? 'username,name' : 'name';
            $response = Http::withToken($this->token())
                ->get($this->baseUrl() . '/' . $userId, ['fields' => $fields]);

            if ($response->successful()) {
                return $response->json('name') ?: $response->json('username');
            }
        } catch (\Throwable $e) {
            // sin permiso o usuario no accesible: se muestra el ID
        }

        return null;
    }
}
