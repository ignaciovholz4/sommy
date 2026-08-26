<?php

namespace App\Services\Ads;

use App\Models\AdSpendDiario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gasto diario de Google Ads via la Google Ads API (REST, GAQL). Requiere
 * developer token aprobado por Google (tramite externo del usuario) mas
 * OAuth2 (client_id/secret de un proyecto de Google Cloud + refresh_token
 * de la cuenta que administra el Google Ads).
 */
class GoogleAdsService
{
    public function habilitado(): bool
    {
        $c = config('services.google_ads');

        return (bool) ($c['enabled'] ?? false)
            && !empty($c['developer_token'])
            && !empty($c['client_id'])
            && !empty($c['client_secret'])
            && !empty($c['refresh_token'])
            && !empty($c['customer_id']);
    }

    private function soloDigitos(?string $valor): string
    {
        return preg_replace('/\D/', '', (string) $valor);
    }

    private function accessToken(): ?string
    {
        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google_ads.client_id'),
                'client_secret' => config('services.google_ads.client_secret'),
                'refresh_token' => config('services.google_ads.refresh_token'),
                'grant_type' => 'refresh_token',
            ])->throw();

            return $response->json('access_token');
        } catch (\Throwable $th) {
            Log::error('GoogleAdsService::accessToken: ' . $th->getMessage());
            return null;
        }
    }

    /** Trae el gasto diario del rango y lo guarda (upsert) en ad_spend_diario. */
    public function sincronizar(\DateTimeInterface $desde, \DateTimeInterface $hasta): int
    {
        if (!$this->habilitado()) {
            return 0;
        }

        $token = $this->accessToken();
        if (!$token) {
            return 0;
        }

        $version = config('services.google_ads.api_version', 'v17');
        $customerId = $this->soloDigitos(config('services.google_ads.customer_id'));
        $loginCustomerId = $this->soloDigitos(config('services.google_ads.login_customer_id'));

        $query = "SELECT segments.date, metrics.cost_micros FROM customer "
            . "WHERE segments.date BETWEEN '{$desde->format('Y-m-d')}' AND '{$hasta->format('Y-m-d')}'";

        try {
            $request = Http::withToken($token)
                ->withHeaders(array_filter([
                    'developer-token' => config('services.google_ads.developer_token'),
                    'login-customer-id' => $loginCustomerId ?: null,
                ]))
                ->acceptJson();

            $response = $request->post(
                "https://googleads.googleapis.com/{$version}/customers/{$customerId}/googleAds:searchStream",
                ['query' => $query]
            )->throw();
        } catch (\Throwable $th) {
            Log::error('GoogleAdsService::sincronizar: ' . $th->getMessage());
            return 0;
        }

        // La API devuelve un array de "batches", cada uno con 'results'
        $porDia = [];
        foreach ($response->json() ?? [] as $batch) {
            foreach ($batch['results'] ?? [] as $fila) {
                $fecha = $fila['segments']['date'] ?? null;
                $costoMicros = (float) ($fila['metrics']['costMicros'] ?? 0);
                if (!$fecha) {
                    continue;
                }
                $porDia[$fecha] = ($porDia[$fecha] ?? 0) + $costoMicros;
            }
        }

        $guardados = 0;
        foreach ($porDia as $fecha => $costoMicros) {
            AdSpendDiario::updateOrCreate(
                ['plataforma' => 'google', 'fecha' => $fecha],
                ['monto' => round($costoMicros / 1_000_000, 2), 'moneda' => config('services.google_ads.moneda', 'ARS'), 'sincronizado_at' => now()]
            );
            $guardados++;
        }

        return $guardados;
    }
}
