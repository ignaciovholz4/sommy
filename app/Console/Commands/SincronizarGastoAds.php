<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsService;
use App\Services\Ads\MetaAdsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Trae el gasto diario de Meta Ads y Google Ads de los ultimos dias (las
 * plataformas revisan el gasto de un dia hasta 72hs despues, por eso se
 * resincroniza una ventana corta en vez de solo "hoy") y lo guarda en
 * ad_spend_diario. Cada plataforma es independiente: si una falla, la otra
 * igual se sincroniza.
 */
class SincronizarGastoAds extends Command
{
    protected $signature = 'ads:sincronizar-gasto {--dias=4 : Cuantos dias hacia atras resincronizar}';

    protected $description = 'Sincroniza el gasto diario de Meta Ads y Google Ads';

    public function handle(MetaAdsService $meta, GoogleAdsService $google): int
    {
        $hasta = now();
        $desde = now()->subDays((int) $this->option('dias'));

        if ($meta->habilitado()) {
            try {
                $guardados = $meta->sincronizar($desde, $hasta);
                $this->info("Meta Ads: {$guardados} dia(s) sincronizado(s).");
            } catch (\Throwable $th) {
                Log::error('ads:sincronizar-gasto (meta): ' . $th->getMessage());
                $this->error('Meta Ads: fallo la sincronizacion, ver logs.');
            }
        } else {
            $this->info('Meta Ads deshabilitado (META_ADS_ENABLED=false), no hay nada que sincronizar.');
        }

        if ($google->habilitado()) {
            try {
                $guardados = $google->sincronizar($desde, $hasta);
                $this->info("Google Ads: {$guardados} dia(s) sincronizado(s).");
            } catch (\Throwable $th) {
                Log::error('ads:sincronizar-gasto (google): ' . $th->getMessage());
                $this->error('Google Ads: fallo la sincronizacion, ver logs.');
            }
        } else {
            $this->info('Google Ads deshabilitado (GOOGLE_ADS_ENABLED=false), no hay nada que sincronizar.');
        }

        return self::SUCCESS;
    }
}
