<?php

namespace App\Services\Ai\ReportesTools;

use Illuminate\Support\Facades\DB;

/** Saldo de caja/bancos (foto actual) e ingresos/egresos de tesoreria en un periodo. */
class TesoreriaQueryTool
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_tesoreria',
            'description' => 'Saldo actual de cada cuenta (caja y banco) y el total de ingresos/egresos de dinero en un rango de fechas. Para deuda de clientes o proveedores usa las otras herramientas, esta es solo movimiento de caja/banco real.',
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

        $saldosCuentas = DB::table('cuentas as cu')
            ->leftJoin('movimientos as m', 'm.cuenta_id', '=', 'cu.id')
            ->where('cu.activa', 1)
            ->groupBy('cu.id', 'cu.nombre', 'cu.tipo')
            ->selectRaw("cu.nombre, cu.tipo,
                COALESCE(SUM(CASE WHEN m.tipo='ingreso' THEN m.total ELSE -m.total END),0) as saldo")
            ->orderByDesc('saldo')->get();

        $saldoTotal = (float) $saldosCuentas->sum('saldo');

        $delPeriodo = DB::table('movimientos')
            ->whereBetween('fecha', [$desde, $hasta])
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo='ingreso' THEN total ELSE 0 END),0) as ingresos,
                COALESCE(SUM(CASE WHEN tipo='egreso' THEN total ELSE 0 END),0) as egresos")
            ->first();

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta],
            'saldo_total_actual' => round($saldoTotal, 2),
            'saldo_por_cuenta' => $saldosCuentas->map(fn ($r) => [
                'cuenta' => $r->nombre,
                'tipo' => $r->tipo,
                'saldo' => round((float) $r->saldo, 2),
            ])->all(),
            'ingresos_periodo' => round((float) $delPeriodo->ingresos, 2),
            'egresos_periodo' => round((float) $delPeriodo->egresos, 2),
            'resultado_periodo' => round((float) $delPeriodo->ingresos - (float) $delPeriodo->egresos, 2),
        ];
    }
}
