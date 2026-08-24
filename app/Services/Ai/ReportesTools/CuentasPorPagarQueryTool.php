<?php

namespace App\Services\Ai\ReportesTools;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Deuda con proveedores (foto actual): total, vencida y proxima a vencer. */
class CuentasPorPagarQueryTool
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_cuentas_por_pagar',
            'description' => 'Deuda actual con proveedores (cuentas por pagar): total, cuanto esta vencido, cuanto vence en los proximos N dias, y el detalle por proveedor. Es una foto del momento, no un periodo.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'dias_proximos' => ['type' => 'integer', 'description' => 'Ventana de dias para "proximos vencimientos" (default 30, maximo 90)'],
                    'limit' => ['type' => 'integer', 'description' => 'Cuantos proveedores devolver en el detalle (maximo 30, default 10)'],
                ],
                'required' => [],
            ],
        ];
    }

    public function execute(array $args): array
    {
        $hoy = Carbon::today();
        $diasProximos = min((int) ($args['dias_proximos'] ?? 30) ?: 30, 90);
        $limit = QueryLimites::limite($args['limit'] ?? 10, 30);

        $debe  = (float) DB::table('proveedor_cc_movimientos')->where('tipo', 'debe')->sum('monto');
        $haber = (float) DB::table('proveedor_cc_movimientos')->where('tipo', 'haber')->sum('monto');

        $vencidas = DB::table('proveedor_cc_movimientos as m')
            ->join('proveedores as p', 'p.idproveedor', '=', 'm.proveedor_id')
            ->where('m.tipo', 'debe')->where('m.estado', '!=', 'pagado')
            ->whereNotNull('m.fecha_vencimiento')->whereDate('m.fecha_vencimiento', '<', $hoy)
            ->groupBy('p.idproveedor', 'p.nombre')
            ->selectRaw('p.nombre, SUM(m.monto) as monto, MIN(m.fecha_vencimiento) as vencimiento')
            ->orderBy('vencimiento')->limit($limit)->get();

        $proximas = DB::table('proveedor_cc_movimientos as m')
            ->join('proveedores as p', 'p.idproveedor', '=', 'm.proveedor_id')
            ->where('m.tipo', 'debe')->where('m.estado', '!=', 'pagado')
            ->whereNotNull('m.fecha_vencimiento')
            ->whereBetween('m.fecha_vencimiento', [$hoy->toDateString(), $hoy->copy()->addDays($diasProximos)->toDateString()])
            ->groupBy('p.idproveedor', 'p.nombre')
            ->selectRaw('p.nombre, SUM(m.monto) as monto, MIN(m.fecha_vencimiento) as vencimiento')
            ->orderBy('vencimiento')->limit($limit)->get();

        return [
            'fecha_consulta' => $hoy->toDateString(),
            'deuda_total' => round($debe - $haber, 2),
            'vencido_total' => round((float) $vencidas->sum('monto'), 2),
            'proximo_total' => round((float) $proximas->sum('monto'), 2),
            'ventana_proximos_dias' => $diasProximos,
            'proveedores_vencidos' => $vencidas->map(fn ($r) => [
                'proveedor' => $r->nombre, 'monto' => round((float) $r->monto, 2), 'vencimiento' => $r->vencimiento,
            ])->all(),
            'proveedores_proximos_a_vencer' => $proximas->map(fn ($r) => [
                'proveedor' => $r->nombre, 'monto' => round((float) $r->monto, 2), 'vencimiento' => $r->vencimiento,
            ])->all(),
        ];
    }
}
