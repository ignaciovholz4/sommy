<?php

namespace App\Services\Ai\ReportesTools;

use Carbon\Carbon;

/**
 * Limites de fecha/filas que se fuerzan del lado del servidor en cada tool
 * del chat de Reportes: el LLM puede pedir lo que quiera, pero nunca se
 * confia ciegamente en el rango o el limite que sugiera.
 */
class QueryLimites
{
    protected const RANGO_MAXIMO_DIAS = 400;

    /** @return array{0: string, 1: string} [desde, hasta] como fecha YYYY-MM-DD, con el rango acotado. */
    public static function rangoFechas(?string $desde, ?string $hasta): array
    {
        $desde = $desde ? Carbon::parse($desde)->startOfDay() : Carbon::now()->startOfMonth();
        $hasta = $hasta ? Carbon::parse($hasta)->endOfDay() : Carbon::now()->endOfDay();

        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        if ($desde->diffInDays($hasta) > self::RANGO_MAXIMO_DIAS) {
            $desde = $hasta->copy()->subDays(self::RANGO_MAXIMO_DIAS)->startOfDay();
        }

        return [$desde->toDateString(), $hasta->toDateString()];
    }

    public static function limite(mixed $pedido, int $maximo): int
    {
        $n = (int) $pedido;

        return $n > 0 ? min($n, $maximo) : min(10, $maximo);
    }
}
