<?php

namespace App\Services\Ai\ReportesTools;

use Illuminate\Support\Facades\DB;

/** Compras a proveedores en un periodo, con desglose por proveedor. */
class ComprasQueryTool
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_compras',
            'description' => 'Total comprado a proveedores en un rango de fechas, con desglose por proveedor.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'desde' => ['type' => 'string', 'description' => 'Fecha inicio YYYY-MM-DD'],
                    'hasta' => ['type' => 'string', 'description' => 'Fecha fin YYYY-MM-DD'],
                    'top_proveedores' => ['type' => 'integer', 'description' => 'Cuantos proveedores devolver (maximo 20, default 10)'],
                ],
                'required' => ['desde', 'hasta'],
            ],
        ];
    }

    public function execute(array $args): array
    {
        [$desde, $hasta] = QueryLimites::rangoFechas($args['desde'] ?? null, $args['hasta'] ?? null);
        $limit = QueryLimites::limite($args['top_proveedores'] ?? 10, 20);

        $total = (float) DB::table('compras')
            ->where('estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('total_con_iva');

        $porProveedor = DB::table('compras as co')
            ->join('proveedores as pr', 'pr.idproveedor', '=', 'co.proveedor_id')
            ->where('co.estado', 'NOT LIKE', 'Cancel%')
            ->whereBetween('co.fecha', [$desde, $hasta])
            ->groupBy('pr.idproveedor', 'pr.nombre')
            ->selectRaw('pr.nombre, COUNT(*) as compras, SUM(co.total_con_iva) as total')
            ->orderByDesc('total')->limit($limit)->get();

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'total_comprado' => round($total, 2),
            'por_proveedor' => $porProveedor->map(fn ($r) => [
                'proveedor' => $r->nombre,
                'compras' => (int) $r->compras,
                'total' => round((float) $r->total, 2),
            ])->all(),
        ];
    }
}
