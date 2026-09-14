<?php

namespace App\Services\Ads;

use App\Models\ecommerce\order_ecommerce;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Conversions API de Meta (server-side). Soporta dos datasets distintos:
 * - Pixel del sitio (services.meta_ads.pixel_id): eventos de navegacion/compra
 *   del ecommerce (ViewContent/AddToCart/InitiateCheckout/Purchase), action_source "website".
 * - Dataset de integracion CRM (services.meta_ads.crm_dataset_id): cambios de
 *   estado de leads que llegan por WhatsApp/Messenger/Instagram, action_source
 *   "system_generated" (formato exigido por el asistente "Enviar un evento de
 *   CRM" del Events Manager de Meta).
 */
class MetaConversionsApiService
{
    public function habilitadoSitio(): bool
    {
        return (bool) config('services.meta_ads.enabled')
            && !empty(config('services.meta_ads.pixel_id'))
            && !empty(config('services.meta_ads.access_token'));
    }

    public function habilitadoCrm(): bool
    {
        return (bool) config('services.meta_ads.enabled')
            && !empty(config('services.meta_ads.crm_dataset_id'))
            && !empty(config('services.meta_ads.crm_access_token'));
    }

    /**
     * Evento del pixel del sitio (Purchase, ViewContent, etc.), action_source "website".
     *
     * @param array $userData ej. ['email' => ..., 'phone' => ..., 'client_ip_address' => ..., 'fbc' => ..., 'fbp' => ...]
     * @param array $customData ej. ['value' => ..., 'currency' => 'ARS']
     */
    public function enviarEventoSitio(string $eventName, string $eventId, array $userData, array $customData = [], ?string $sourceUrl = null): bool
    {
        if (!$this->habilitadoSitio()) {
            return false;
        }

        return $this->postEvento(
            datasetId: config('services.meta_ads.pixel_id'),
            accessToken: config('services.meta_ads.access_token'),
            eventName: $eventName,
            eventId: $eventId,
            actionSource: 'website',
            userData: $userData,
            customData: $customData,
            sourceUrl: $sourceUrl,
        );
    }

    /**
     * Evento de cambio de estado de lead (dataset CRM), action_source "system_generated".
     * Ej.: 'Lead' cuando nace una conversacion de WhatsApp/Messenger/IG desde un anuncio.
     */
    public function enviarEventoCrm(string $eventName, string $eventId, array $userData): bool
    {
        if (!$this->habilitadoCrm()) {
            return false;
        }

        return $this->postEvento(
            datasetId: config('services.meta_ads.crm_dataset_id'),
            accessToken: config('services.meta_ads.crm_access_token'),
            eventName: $eventName,
            eventId: $eventId,
            actionSource: 'system_generated',
            userData: $userData,
            customData: [
                'event_source' => 'crm',
                'lead_event_source' => 'Sommy',
            ],
        );
    }

    /**
     * Dispara el evento Purchase de un pedido pagado (idempotente por
     * order_ecommerce.purchase_capi_sent_at). Se llama desde el webhook de
     * MercadoPago y desde el cambio manual a "Pagado" en OrderController —
     * los dos caminos por los que hoy una orden llega a pagada.
     */
    public function dispararPurchase(order_ecommerce $order): bool
    {
        if ($order->purchase_capi_sent_at) {
            return false;
        }

        $cliente = $order->cliente;

        $enviado = $this->enviarEventoSitio(
            'Purchase',
            'purchase_' . $order->order_id,
            [
                'email' => $cliente->email ?? null,
                'phone' => $cliente->telefono ?? null,
                'fbclid' => $order->fbclid,
            ],
            [
                'value' => (float) $order->total_amount,
                'currency' => config('services.meta_ads.moneda', 'ARS'),
            ],
            url('/pedido/gracias/' . $order->order_id)
        );

        if ($enviado) {
            $order->update(['purchase_capi_sent_at' => now()]);
        }

        return $enviado;
    }

    private function postEvento(string $datasetId, string $accessToken, string $eventName, string $eventId, string $actionSource, array $userData, array $customData, ?string $sourceUrl = null): bool
    {
        $version = config('services.meta_ads.graph_version', 'v21.0');

        $evento = [
            'event_name' => $eventName,
            'event_time' => now()->timestamp,
            'event_id' => $eventId,
            'action_source' => $actionSource,
            'user_data' => $this->hashearUserData($userData),
            'custom_data' => $customData,
        ];
        if ($sourceUrl) {
            $evento['event_source_url'] = $sourceUrl;
        }

        try {
            Http::withToken($accessToken)
                ->acceptJson()
                ->post("https://graph.facebook.com/{$version}/{$datasetId}/events", [
                    'data' => [$evento],
                ])->throw();

            return true;
        } catch (\Throwable $th) {
            Log::error("MetaConversionsApiService::postEvento ({$eventName}, dataset {$datasetId}): " . $th->getMessage());
            return false;
        }
    }

    private function hashearUserData(array $userData): array
    {
        $out = [];

        if (!empty($userData['email'])) {
            $out['em'] = [hash('sha256', strtolower(trim($userData['email'])))];
        }
        if (!empty($userData['phone'])) {
            // Meta espera el telefono en E.164 sin "+" antes de hashear
            $telefono = preg_replace('/\D/', '', $userData['phone']);
            $out['ph'] = [hash('sha256', $telefono)];
        }

        // Campos que NO se hashean
        foreach (['client_ip_address', 'client_user_agent', 'fbc', 'fbp'] as $campo) {
            if (!empty($userData[$campo])) {
                $out[$campo] = $userData[$campo];
            }
        }

        // fbclid crudo (guardado en order_ecommerce.fbclid) sin el formato "fbc"
        // que exige Meta (fb.1.<timestamp_ms>.<fbclid>) — lo armamos aca. El
        // timestamp no es el del click real (no se guarda), es una aproximacion
        // con la hora de envio del evento; igual sirve para el matching.
        if (empty($out['fbc']) && !empty($userData['fbclid'])) {
            $out['fbc'] = 'fb.1.' . now()->getTimestampMs() . '.' . $userData['fbclid'];
        }

        return $out;
    }
}
