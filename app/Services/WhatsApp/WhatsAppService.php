<?php

namespace App\Services\WhatsApp;

use App\Models\WaAccount;
use Illuminate\Support\Facades\Http;

/**
 * Cliente de la WhatsApp Business Cloud API (Meta Graph).
 * Los envios de mensajes de la bandeja/bot NO llaman esto directo:
 * pasan por el job SendWhatsAppMessage para ser asincronicos y reintentables.
 */
class WhatsAppService
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
        return $this->account->token ?: config('services.whatsapp.token');
    }

    protected function baseUrl(): string
    {
        $version = config('services.whatsapp.graph_version', 'v21.0');
        return "https://graph.facebook.com/{$version}";
    }

    protected function messagesUrl(): string
    {
        return $this->baseUrl() . '/' . $this->account->phone_number_id . '/messages';
    }

    /**
     * Envia un payload de mensaje crudo. Devuelve la respuesta JSON de Meta.
     * Lanza excepcion si Meta responde error (el job la captura y marca failed).
     */
    public function send(array $payload): array
    {
        $response = Http::withToken($this->token())
            ->acceptJson()
            ->post($this->messagesUrl(), array_merge([
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
            ], $payload));

        if ($response->failed()) {
            $error = $response->json('error') ?? ['message' => $response->body()];
            throw new WhatsAppApiException(
                $error['message'] ?? 'Error desconocido de Meta',
                (string) ($error['code'] ?? $response->status()),
                $error
            );
        }

        return $response->json();
    }

    public function buildTextPayload(string $to, string $body): array
    {
        return [
            'to' => $to,
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $body],
        ];
    }

    /**
     * @param array $bodyParams valores para {{1}}, {{2}}... en orden
     */
    public function buildTemplatePayload(string $to, string $name, string $language, array $bodyParams = []): array
    {
        $components = [];
        if ($bodyParams) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(
                    fn ($p) => ['type' => 'text', 'text' => (string) $p],
                    array_values($bodyParams)
                ),
            ];
        }

        return [
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $name,
                'language' => ['code' => $language],
                'components' => $components,
            ],
        ];
    }

    /**
     * Marca un mensaje entrante como leido (doble tilde azul del lado del cliente).
     */
    public function markAsRead(string $waMessageId): void
    {
        Http::withToken($this->token())->post($this->messagesUrl(), [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $waMessageId,
        ]);
    }

    /**
     * Obtiene la URL temporal de descarga de un media_id.
     */
    public function getMediaUrl(string $mediaId): ?array
    {
        $response = Http::withToken($this->token())
            ->get($this->baseUrl() . '/' . $mediaId);

        if ($response->failed()) {
            return null;
        }

        return $response->json(); // {url, mime_type, file_size, ...}
    }

    /**
     * Descarga el binario de un media (la URL exige el mismo token).
     */
    public function downloadMedia(string $url): ?string
    {
        $response = Http::withToken($this->token())->get($url);

        return $response->successful() ? $response->body() : null;
    }

    /**
     * Trae las plantillas de la WABA para sincronizar estado de aprobacion.
     */
    public function fetchTemplates(): array
    {
        $wabaId = $this->account->waba_id ?: config('services.whatsapp.waba_id');
        if (!$wabaId) {
            return [];
        }

        $response = Http::withToken($this->token())
            ->get($this->baseUrl() . "/{$wabaId}/message_templates", ['limit' => 100]);

        return $response->successful() ? ($response->json('data') ?? []) : [];
    }

    /**
     * Cuenta por defecto: la activa en BD, o una creada desde el .env
     * (util en desarrollo antes de cargar cuentas desde la UI).
     */
    public static function defaultAccount(): ?WaAccount
    {
        $account = WaAccount::where('activo', true)->first();
        if ($account) {
            return $account;
        }

        $phoneNumberId = config('services.whatsapp.phone_number_id');
        if (!$phoneNumberId) {
            return null;
        }

        return WaAccount::firstOrCreate(
            ['phone_number_id' => $phoneNumberId],
            [
                'nombre' => 'Principal',
                'waba_id' => config('services.whatsapp.waba_id'),
                'verify_token' => config('services.whatsapp.verify_token'),
                'activo' => true,
            ]
        );
    }
}
