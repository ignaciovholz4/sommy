<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

/** Borra logs de auditoria de mas de 180 dias, para que la tabla no crezca sin limite. */
class LimpiarAuditoria extends Command
{
    protected $signature = 'auditoria:limpiar {--dias=180}';

    protected $description = 'Borra logs de auditoria mas viejos que N dias (default 180)';

    public function handle(): int
    {
        $borrados = AuditLog::where('created_at', '<', now()->subDays((int) $this->option('dias')))->delete();

        $this->info("Se borraron {$borrados} log(s) de auditoria.");

        return self::SUCCESS;
    }
}
