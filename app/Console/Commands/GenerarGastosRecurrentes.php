<?php

namespace App\Console\Commands;

use App\Models\Gasto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Genera los gastos recurrentes vencidos: por cada gasto recurrente cuyo
 * próximo vencimiento ya pasó, clona un gasto pendiente con esa fecha y
 * avanza el próximo vencimiento según la frecuencia.
 *
 * NO registrado en el Kernel: ver docs/FINANZAS_NOTES.md para el snippet de scheduler.
 */
class GenerarGastosRecurrentes extends Command
{
    protected $signature = 'gastos:generar-recurrentes';

    protected $description = 'Genera los gastos pendientes de las plantillas recurrentes vencidas (alquiler, servicios, etc.)';

    public function handle(): int
    {
        $hoy = now()->toDateString();

        $recurrentes = Gasto::where('es_recurrente', true)
            ->whereNotNull('frecuencia')
            ->whereNotNull('proximo_vencimiento')
            ->whereDate('proximo_vencimiento', '<=', $hoy)
            ->get();

        $generados = 0;

        foreach ($recurrentes as $plantilla) {
            try {
                DB::transaction(function () use ($plantilla, &$generados) {
                    // Puede haber más de un período atrasado: se generan todos hasta hoy
                    while ($plantilla->proximo_vencimiento && $plantilla->proximo_vencimiento->lte(now())) {
                        Gasto::create([
                            'fecha'              => $plantilla->proximo_vencimiento->toDateString(),
                            'gasto_categoria_id' => $plantilla->gasto_categoria_id,
                            'proveedor_id'       => $plantilla->proveedor_id,
                            'descripcion'        => $plantilla->descripcion,
                            'monto'              => $plantilla->monto,
                            'es_recurrente'      => false,
                            'user_id'            => $plantilla->user_id,
                            'sucursal_id'        => $plantilla->sucursal_id,
                            'estado'             => 'pendiente',
                        ]);

                        $plantilla->proximo_vencimiento = match ($plantilla->frecuencia) {
                            'semanal' => $plantilla->proximo_vencimiento->copy()->addWeek(),
                            'mensual' => $plantilla->proximo_vencimiento->copy()->addMonthNoOverflow(),
                            'anual'   => $plantilla->proximo_vencimiento->copy()->addYear(),
                        };

                        $generados++;
                    }

                    $plantilla->save();
                });
            } catch (\Throwable $e) {
                Log::error('Error generando gasto recurrente #' . $plantilla->id . ': ' . $e->getMessage());
                $this->error('Error con el gasto #' . $plantilla->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Listo: se generaron {$generados} gastos a partir de " . $recurrentes->count() . ' plantillas recurrentes.');

        return self::SUCCESS;
    }
}
