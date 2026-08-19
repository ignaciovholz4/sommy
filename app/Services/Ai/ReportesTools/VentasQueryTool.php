<?php

namespace App\Services\Ai\ReportesTools;

use Illuminate\Support\Facades\DB;

/**
 * Facturacion, cantidad de ventas y productos mas vendidos en un periodo.
 * Los limites de fecha/filas se fuerzan aca: nunca se confia en lo que pida el LLM.
 */
class VentasQueryTool
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_ventas',
            'description' => 'Facturacion, cantidad de ventas, ticket promedio y productos mas vendidos en un rango de fechas. Opcionalmente filtrado por sucursal.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'desde' => ['type' => 'string', 'description' => 'Fecha inicio YYYY-MM-DD'],
                    'hasta' => ['type' => 'string', 'description' => 'Fecha fin YYYY-MM-DD'],
                    'sucursal_id' => ['type' => 'integer', 'description' => 'Filtrar por una sucursal (opcional)'],
                    'top_productos' => ['type' => 'integer', 'description' => 'Cuantos productos top devolver (maximo 20, default 10)'],
                ],
                'required' => ['desde', 'hasta'],
            ],
        ];
    }

    public function execute(array $args): array
    {
        [$desde, $hasta] = QueryLimites::rangoFechas($args['desde'] ?? null, $args['hasta'] ?? null);
        $limit = QueryLimites::limite($args['top_productos'] ?? 10, 20);

        $ventasQuery = DB::table('ventas')
            ->where('estado', 'NOT LIKE', 'Cancel%')
            ->where('estado', 'NOT LIKE', 'Anul%')
            ->whereBetween('fecha', [$desde, $hasta]);

        if (!empty($args['sucursal_id'])) {
            $ventasQuery->where('sucursal_id', (int) $args['sucursal_id']);
        }

        $facturacion = (float) (clone $ventasQuery)->sum('total_con_iva');
        $cantidad = (int) (clone $ventasQuery)->count();

        $detalleBase = DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.idventa', '=', 'dv.venta_id')
            ->join('productos as p', 'p.idarticulo', '=', 'dv.articulo_id')
            ->where('v.estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('v.fecha', [$desde, $hasta]);

        if (!empty($args['sucursal_id'])) {
            $detalleBase->where('v.sucursal_id', (int) $args['sucursal_id']);
        }

        $topProductos = (clone $detalleBase)
            ->groupBy('p.idarticulo', 'p.nombre')
            ->selectRaw('p.nombre, SUM(dv.cantidad) as unidades, SUM(dv.subtotal_con_iva) as facturado')
            ->orderByDesc('facturado')->limit($limit)->get();

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'facturacion_total' => round($facturacion, 2),
            'cantidad_ventas' => $cantidad,
            'ticket_promedio' => $cantidad ? round($facturacion / $cantidad, 2) : 0,
            'top_productos' => $topProductos->map(fn ($r) => [
                'producto' => $r->nombre,
                'unidades' => (int) $r->unidades,
                'facturado' => round((float) $r->facturado, 2),
            ])->all(),
        ];
    }
}
