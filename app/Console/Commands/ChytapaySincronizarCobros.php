<?php

namespace App\Console\Commands;

use App\Models\ChytapayConexion;
use App\Services\Chytapay\ChytapayPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Trae los cobros pagados de todas las cuentas conectadas a Chytapay y los
 * vuelca como movimientos_bancarios_importados pendientes de conciliar.
 */
class ChytapaySincronizarCobros extends Command
{
    protected $signature = 'chytapay:sincronizar-cobros';

    protected $description = 'Sincroniza los cobros pagados de Chytapay hacia la conciliacion bancaria de cada cuenta conectada';

    public function handle(ChytapayPaymentService $pagos): int
    {
        if (!config('services.chytapay.enabled')) {
            $this->info('Chytapay deshabilitado (CHYTAPAY_ENABLED=false), no hay nada que sincronizar.');
            return self::SUCCESS;
        }

        $conexiones = ChytapayConexion::all();

        foreach ($conexiones as $conexion) {
            try {
                $creados = $pagos->sincronizar($conexion);
                $this->info("Cuenta {$conexion->cuenta_id}: {$creados} cobro(s) nuevo(s).");
            } catch (\Throwable $th) {
                Log::error('chytapay:sincronizar-cobros: ' . $th->getMessage(), ['cuenta_id' => $conexion->cuenta_id]);
                $this->error("Cuenta {$conexion->cuenta_id}: fallo la sincronizacion, ver logs.");
            }
        }

        return self::SUCCESS;
    }
}
