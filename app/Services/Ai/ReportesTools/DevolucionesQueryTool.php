<?php

namespace App\Services\Ai\ReportesTools;

use Illuminate\Support\Facades\DB;

/** Devoluciones/anulaciones (venta, compra o pedido) en un periodo. */
class DevolucionesQueryTool
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_devoluciones',
            'description' => 'Devoluciones y anulaciones (de ventas, compras o pedidos online) en un rango de fechas: cantidad, monto total y desglose por tipo y por motivo.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'desde' => ['type' => 'string', 'description' => 'Fecha inicio YYYY-MM-DD'],
                    'hasta' => ['type' => 'string', 'description' => 'Fecha fin YYYY-MM-DD'],
                ],
                'required' => ['desde', 'hasta'],
            ],
        ];
    }

    public function execute(array $args): array
    {
        [$desde, $hasta] = QueryLimites::rangoFechas($args['desde'] ?? null, $args['hasta'] ?? null);

        $base = DB::table('devoluciones')->whereBetween('fecha', [$desde, $hasta]);

        $totalRow = (clone $base)->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(monto),0) as monto')->first();

        $porTipo = (clone $base)
            ->groupBy('tipo')
            ->selectRaw('tipo, COUNT(*) as cantidad, COALESCE(SUM(monto),0) as monto')
            ->orderByDesc('monto')->get();

        $porMotivo = (clone $base)
            ->whereNotNull('motivo')->where('motivo', '!=', '')
            ->groupBy('motivo')
            ->selectRaw('motivo, COUNT(*) as cantidad, COALESCE(SUM(monto),0) as monto')
            ->orderByDesc('cantidad')->limit(10)->get();

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'cantidad_total' => (int) $totalRow->cantidad,
            'monto_total' => round((float) $totalRow->monto, 2),
            'por_tipo' => $porTipo->map(fn ($r) => [
                'tipo' => $r->tipo, 'cantidad' => (int) $r->cantidad, 'monto' => round((float) $r->monto, 2),
            ])->all(),
            'principales_motivos' => $porMotivo->map(fn ($r) => [
                'motivo' => $r->motivo, 'cantidad' => (int) $r->cantidad, 'monto' => round((float) $r->monto, 2),
            ])->all(),
        ];
    }
}
