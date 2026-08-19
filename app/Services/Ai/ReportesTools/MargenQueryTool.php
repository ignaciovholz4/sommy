<?php

namespace App\Services\Ai\ReportesTools;

use Illuminate\Support\Facades\DB;

/** Margen bruto (venta - costo de compra) en un periodo. */
class MargenQueryTool
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_margen',
            'description' => 'Margen bruto (facturado menos costo de compra de lo vendido) en un rango de fechas.',
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

        $row = DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.idventa', '=', 'dv.venta_id')
            ->join('productos as p', 'p.idarticulo', '=', 'dv.articulo_id')
            ->where('v.estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('v.fecha', [$desde, $hasta])
            ->selectRaw('COALESCE(SUM(dv.subtotal_con_iva),0) as venta, COALESCE(SUM(dv.cantidad * p.pcompra_con_iva),0) as costo')
            ->first();

        $venta = (float) $row->venta;
        $costo = (float) $row->costo;
        $margen = $venta - $costo;

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'venta' => round($venta, 2),
            'costo' => round($costo, 2),
            'margen' => round($margen, 2),
            'margen_pct' => $venta > 0 ? round(($margen / $venta) * 100, 2) : 0,
        ];
    }
}
