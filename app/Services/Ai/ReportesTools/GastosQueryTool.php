<?php

namespace App\Services\Ai\ReportesTools;

use Illuminate\Support\Facades\DB;

/** Gastos operativos (Ads, IA, fletes, alquiler, etc.) en un periodo, con desglose por categoria. */
class GastosQueryTool
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_gastos',
            'description' => 'Gastos operativos del negocio (publicidad, IA, fletes, alquiler, sueldos, etc.) en un rango de fechas, con desglose por categoria. Por default solo cuenta los gastos ya pagados.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'desde' => ['type' => 'string', 'description' => 'Fecha inicio YYYY-MM-DD'],
                    'hasta' => ['type' => 'string', 'description' => 'Fecha fin YYYY-MM-DD'],
                    'incluir_pendientes' => ['type' => 'boolean', 'description' => 'Si es true, incluye tambien gastos cargados pero no pagados todavia (default false)'],
                    'categoria' => ['type' => 'string', 'description' => 'Filtra por nombre de categoria (busqueda parcial, ej: "ads", "IA")'],
                ],
                'required' => ['desde', 'hasta'],
            ],
        ];
    }

    public function execute(array $args): array
    {
        [$desde, $hasta] = QueryLimites::rangoFechas($args['desde'] ?? null, $args['hasta'] ?? null);
        $incluirPendientes = (bool) ($args['incluir_pendientes'] ?? false);
        $categoria = trim((string) ($args['categoria'] ?? ''));

        $base = DB::table('gastos as g')
            ->join('gasto_categorias as c', 'c.id', '=', 'g.gasto_categoria_id')
            ->whereBetween('g.fecha', [$desde, $hasta]);

        if (!$incluirPendientes) {
            $base->where('g.estado', 'pagado');
        }
        if ($categoria !== '') {
            $base->where('c.nombre', 'like', '%' . $categoria . '%');
        }

        $total = (float) (clone $base)->sum('g.monto');

        $porCategoria = (clone $base)
            ->groupBy('c.id', 'c.nombre')
            ->selectRaw('c.nombre, SUM(g.monto) as total, COUNT(*) as cantidad')
            ->orderByDesc('total')->get();

        $pendientesTotal = (float) DB::table('gastos')
            ->where('estado', 'pendiente')
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('monto');

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'incluye_pendientes' => $incluirPendientes,
            'total_gastado' => round($total, 2),
            'pendientes_de_pago_en_el_periodo' => round($pendientesTotal, 2),
            'por_categoria' => $porCategoria->map(fn ($r) => [
                'categoria' => $r->nombre,
                'total' => round((float) $r->total, 2),
                'cantidad' => (int) $r->cantidad,
            ])->all(),
        ];
    }
}
