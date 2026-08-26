<?php

namespace App\Services\Ads;

use App\Models\AdSpendDiario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gasto diario de Meta Ads (Facebook/Instagram) via Marketing API.
 * Usa un access token con permiso ads_read sobre la cuenta publicitaria
 * (distinto del token de mensajeria de WhatsApp/Graph que ya usa el bot).
 */
class MetaAdsService
{
    public function habilitado(): bool
    {
        return (bool) config('services.meta_ads.enabled')
            && !empty(config('services.meta_ads.access_token'))
            && !empty(config('services.meta_ads.ad_account_id'));
    }

    /** Trae el gasto diario del rango y lo guarda (upsert) en ad_spend_diario. */
    public function sincronizar(\DateTimeInterface $desde, \DateTimeInterface $hasta): int
    {
        if (!$this->habilitado()) {
            return 0;
        }

        $version = config('services.meta_ads.graph_version', 'v21.0');
        $adAccountId = config('services.meta_ads.ad_account_id');

        try {
            $response = Http::withToken(config('services.meta_ads.access_token'))
                ->acceptJson()
                ->get("https://graph.facebook.com/{$version}/act_{$adAccountId}/insights", [
                    'level' => 'account',
                    'time_increment' => 1,
                    'fields' => 'spend',
                    'time_range' => json_encode([
                        'since' => $desde->format('Y-m-d'),
                        'until' => $hasta->format('Y-m-d'),
                    ]),
                ])->throw();
        } catch (\Throwable $th) {
            Log::error('MetaAdsService::sincronizar: ' . $th->getMessage());
            return 0;
        }

        $guardados = 0;
        foreach ($response->json('data') ?? [] as $dia) {
            if (!isset($dia['date_start'], $dia['spend'])) {
                continue;
            }

            AdSpendDiario::updateOrCreate(
                ['plataforma' => 'meta', 'fecha' => $dia['date_start']],
                ['monto' => (float) $dia['spend'], 'moneda' => config('services.meta_ads.moneda', 'ARS'), 'sincronizado_at' => now()]
            );
            $guardados++;
        }

        return $guardados;
    }
}
