<?php

namespace App\Console\Commands;

use App\Models\Cheque;
use App\Models\Notificacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Avisa de cheques vencidos o por vencer (≤ 3 días): los de terceros para
 * recordar cobrarlos/depositarlos, los propios para saber qué se va a
 * debitar. Mismo patrón "avisar solo si cambió el set" que stock:alertar-critico.
 */
class AlertarVencimientosCheques extends Command
{
    protected $signature = 'cheques:alertar-vencimientos';

    protected $description = 'Notifica cheques (propios y de terceros) vencidos o por vencer en los próximos 3 días';

    public function handle(): int
    {
        $limite = now()->addDays(3)->toDateString();

        $porCobrar = Cheque::where('tipo', 'tercero')
            ->whereIn('estado', ['en_cartera', 'depositado'])
            ->whereDate('fecha_cobro', '<=', $limite)
            ->orderBy('fecha_cobro')
            ->get();

        $porDebitar = Cheque::where('tipo', 'propio')
            ->where('estado', 'en_cartera')
            ->whereDate('fecha_cobro', '<=', $limite)
            ->orderBy('fecha_cobro')
            ->get();

        $this->avisarSiCambio('cheques_por_cobrar', $porCobrar, fn ($items) => Notificacion::avisar('cheque',
            '💰 ' . $items->count() . ' cheque(s) de clientes para cobrar',
            $items->take(4)->map(fn ($c) => '#' . ($c->numero ?: $c->id) . ' $' . number_format($c->monto, 0, ',', '.') . ' (' . $c->fecha_cobro->format('d/m') . ')')->implode(', ') . ($items->count() > 4 ? '…' : ''),
            url('finanzas/cheques'), 'alerta'));

        $this->avisarSiCambio('cheques_por_debitar', $porDebitar, fn ($items) => Notificacion::avisar('cheque',
            '📤 ' . $items->count() . ' cheque(s) propio(s) por debitarse',
            $items->take(4)->map(fn ($c) => '#' . ($c->numero ?: $c->id) . ' $' . number_format($c->monto, 0, ',', '.') . ' (' . $c->fecha_cobro->format('d/m') . ')')->implode(', ') . ($items->count() > 4 ? '…' : ''),
            url('finanzas/cheques'), 'alerta'));

        $this->info('Por cobrar: ' . $porCobrar->count() . ' · Por debitar: ' . $porDebitar->count());

        return self::SUCCESS;
    }

    protected function avisarSiCambio(string $clave, $items, callable $notificar): void
    {
        $ids = $items->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $cacheKey = "alerta_{$clave}_ids";

        if (empty($ids)) {
            Cache::forget($cacheKey);
            return;
        }

        if (Cache::get($cacheKey) === $ids) {
            return;
        }

        $notificar($items);
        Cache::forever($cacheKey, $ids);
    }
}
