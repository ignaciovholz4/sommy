<?php

namespace App\Console\Commands;

use App\Services\Cobranzas\CobranzaAgentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Genera borradores de recordatorio de cobranza para clientes con deuda
 * vencida. Nunca envia: los deja en cola (cobranza_recordatorios) para que
 * un humano los apruebe desde Finanzas > Cobranzas.
 */
class GenerarRecordatoriosCobranza extends Command
{
    protected $signature = 'cobranzas:generar-recordatorios';

    protected $description = 'Genera borradores de recordatorio de cobranza para deuda vencida de clientes';

    public function handle(CobranzaAgentService $service): int
    {
        $resultado = $service->generarBorradores();

        $this->info("Recordatorios generados (pendientes de revision): {$resultado['generados']}");

        if (!empty($resultado['omitidos_sin_plantilla'])) {
            $this->warn(count($resultado['omitidos_sin_plantilla']) . ' cliente(s) con deuda vencida pero sin plantilla de WhatsApp aprobada para su nivel de cobranza:');
            foreach ($resultado['omitidos_sin_plantilla'] as $c) {
                $this->line("- {$c}");
            }
        }

        if (!empty($resultado['omitidos_sin_telefono'])) {
            $this->warn(count($resultado['omitidos_sin_telefono']) . ' cliente(s) con deuda vencida pero sin telefono valido para WhatsApp:');
            foreach ($resultado['omitidos_sin_telefono'] as $c) {
                $this->line("- {$c}");
            }
        }

        Log::info('Cobranzas: ' . $resultado['generados'] . ' recordatorios generados, '
            . count($resultado['omitidos_sin_plantilla']) . ' sin plantilla, '
            . count($resultado['omitidos_sin_telefono']) . ' sin telefono.');

        return self::SUCCESS;
    }
}
