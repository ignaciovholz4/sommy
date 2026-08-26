<?php

namespace App\Services\Chytapay;

use App\Models\ChytapayConexion;
use App\Models\MovimientoImportado;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Trae los cobros (payment-request) pagados de Chytapay y los vuelca como
 * filas "pendiente" en movimientos_bancarios_importados, el mismo lugar
 * donde caen los extractos subidos a mano en ConciliacionController. Solo
 * cubre ingresos: la API de Chytapay pegada no expone egresos/transferencias
 * salientes, esas siguen con el flujo manual de conciliacion.
 */
class ChytapayPaymentService
{
    public function __construct(private ChytapayAuthService $auth)
    {
    }

    /**
     * Sincroniza los cobros pagados desde el ultimo sync (o los ultimos 30
     * dias si nunca sincronizo) hasta hoy. Devuelve cuantas filas nuevas creo.
     */
    public function sincronizar(ChytapayConexion $conexion): int
    {
        $conexion = $this->auth->refreshIfNeeded($conexion);

        $desde = $conexion->last_sync_at ? $conexion->last_sync_at->copy()->subDay() : now()->subDays(30);

        try {
            $response = Http::withToken($conexion->id_token)
                ->acceptJson()
                ->get(config('services.chytapay.api_base_url') . '/payment-request', [
                    'startDate' => $desde->format('Y-m-d'),
                    'endDate' => now()->format('Y-m-d'),
                ])->throw();
        } catch (\Throwable $th) {
            Log::error('Chytapay sincronizar: ' . $th->getMessage(), ['cuenta_id' => $conexion->cuenta_id]);
            return 0;
        }

        $creados = 0;

        foreach ($response->json() ?? [] as $cobro) {
            if ($this->importarCobro($conexion, $cobro)) {
                $creados++;
            }
        }

        $conexion->update(['last_sync_at' => now()]);

        return $creados;
    }

    /**
     * Crea el MovimientoImportado para un cobro ya pagado en su totalidad,
     * si todavia no existe (dedupe por chytapay_payment_request_id). Los
     * cobros sin pagar o parciales se ignoran hasta que se completen.
     */
    private function importarCobro(ChytapayConexion $conexion, array $cobro): bool
    {
        $id = $cobro['paymentRequestId'] ?? null;
        $monto = (float) ($cobro['amount'] ?? 0);
        $pagado = (float) ($cobro['paidAmount'] ?? 0);

        if (!$id || $pagado <= 0 || $pagado < $monto) {
            return false;
        }

        if (MovimientoImportado::where('chytapay_payment_request_id', $id)->exists()) {
            return false;
        }

        $cliente = $cobro['customer'] ?? [];
        $descripcion = trim(($cliente['name'] ?? '') . ' ' . ($cliente['email'] ?? $cliente['phoneNumber'] ?? ''));

        try {
            MovimientoImportado::create([
                'cuenta_id' => $conexion->cuenta_id,
                'origen' => 'chytapay',
                'chytapay_payment_request_id' => $id,
                'fecha' => $cobro['createdAt'] ?? now(),
                'tipo' => 'ingreso',
                'monto' => $pagado,
                'descripcion' => $descripcion !== '' ? $descripcion : 'Cobro Chytapay',
                'referencia' => $cobro['referenceId'] ?? null,
                'fila_original' => $cobro,
                'estado' => 'pendiente',
            ]);

            return true;
        } catch (\Throwable $th) {
            // Ej: choque con el unique compuesto si el mismo cobro ya entro por otra via
            Log::warning('Chytapay importarCobro: ' . $th->getMessage(), ['payment_request_id' => $id]);
            return false;
        }
    }
}
