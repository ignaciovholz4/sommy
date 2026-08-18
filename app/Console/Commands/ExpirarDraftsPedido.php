<?php

namespace App\Console\Commands;

use App\Models\WaOrderDraft;
use Illuminate\Console\Command;

class ExpirarDraftsPedido extends Command
{
    protected $signature = 'whatsapp:expirar-drafts {--dias=7 : Días de antigüedad para expirar}';

    protected $description = 'Expira borradores de pedido de WhatsApp sin actividad (cotizaciones abandonadas)';

    public function handle(): int
    {
        $dias = (int) $this->option('dias');

        $expirados = WaOrderDraft::whereIn('status', ['borrador', 'pendiente_confirmacion'])
            ->where('updated_at', '<', now()->subDays($dias))
            ->update(['status' => 'expirado']);

        $this->info("Borradores expirados: {$expirados}");

        return self::SUCCESS;
    }
}
