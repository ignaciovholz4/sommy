<?php

namespace App\Services;

use App\Models\ClarityInsight;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Data Export API de Microsoft Clarity: metricas agregadas (sesiones,
 * engagement, rage/dead clicks, scroll, etc.) — NO el mapa de calor visual
 * ni las grabaciones de sesion, eso solo se ve en clarity.microsoft.com.
 *
 * Limite duro de la API: 10 llamadas por dia por proyecto, maximo 3 dias de
 * datos por llamada. Por eso se guarda un snapshot por dia (updateOrCreate
 * por fecha) y se protege contra sincronizar mas de una vez cada pocas horas.
 */
class ClarityService
{
    public function habilitado(): bool
    {
        return !empty(config('services.clarity.api_token'));
    }

    /** Resguardo del cupo diario: no sincronizar si ya se hizo hace poco. */
    public function puedeSincronizar(): bool
    {
        $ultima = ClarityInsight::latest('sincronizado_at')->first();

        return !$ultima || !$ultima->sincronizado_at || $ultima->sincronizado_at->diffInHours(now()) >= 3;
    }

    public function sincronizar(): bool
    {
        if (!$this->habilitado() || !$this->puedeSincronizar()) {
            return false;
        }

        try {
            $response = Http::withToken(config('services.clarity.api_token'))
                ->acceptJson()
                ->get('https://www.clarity.ms/export-data/api/v1/project-live-insights', [
                    'numOfDays' => 3,
                ])->throw();
        } catch (\Throwable $th) {
            Log::error('ClarityService::sincronizar: ' . $th->getMessage());
            return false;
        }

        ClarityInsight::updateOrCreate(
            ['fecha' => now()->toDateString()],
            ['payload' => $response->json(), 'sincronizado_at' => now()]
        );

        return true;
    }

    public function ultimoSnapshot(): ?ClarityInsight
    {
        return ClarityInsight::latest('fecha')->first();
    }
}
