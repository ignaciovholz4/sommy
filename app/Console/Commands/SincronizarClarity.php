<?php

namespace App\Console\Commands;

use App\Services\ClarityService;
use Illuminate\Console\Command;

/** Sincroniza el snapshot diario de metricas de Microsoft Clarity (Data Export API). */
class SincronizarClarity extends Command
{
    protected $signature = 'clarity:sincronizar';

    protected $description = 'Sincroniza las metricas agregadas de Microsoft Clarity (Data Export API)';

    public function handle(ClarityService $clarity): int
    {
        if (!$clarity->habilitado()) {
            $this->info('Clarity Data Export deshabilitado (falta CLARITY_API_TOKEN en Integraciones).');
            return self::SUCCESS;
        }

        $ok = $clarity->sincronizar();
        $this->info($ok ? 'Clarity: snapshot sincronizado.' : 'Clarity: no se sincronizo (cupo diario protegido o error, ver logs).');

        return self::SUCCESS;
    }
}
