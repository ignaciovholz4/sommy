<?php

namespace App\Services\Ai\ReportesTools;

use Illuminate\Support\Facades\DB;

/** Deuda total de clientes en cuenta corriente y principales deudores (foto actual). */
class DeudoresQueryTool
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_deudores',
            'description' => 'Deuda total de clientes en cuenta corriente (foto actual, no un periodo) y el ranking de principales deudores.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'limit' => ['type' => 'integer', 'description' => 'Cuantos deudores devolver (maximo 30, default 10)'],
                ],
                'required' => [],
            ],
        ];
    }

    public function execute(array $args): array
    {
        $limit = QueryLimites::limite($args['limit'] ?? 10, 30);

        $deudaTotal = (float) DB::table('cliente_cc_movimientos')
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo='cargo' THEN monto ELSE -monto END),0) as saldo")
            ->value('saldo');

        $topDeudores = DB::table('cliente_cc_movimientos as m')
            ->join('clientes as c', 'c.idcliente', '=', 'm.cliente_id')
            ->groupBy('c.idcliente', 'c.nombre', 'c.paterno')
            ->selectRaw("CONCAT(c.nombre,' ',COALESCE(c.paterno,'')) as nombre,
                COALESCE(SUM(CASE WHEN m.tipo='cargo' THEN m.monto ELSE -m.monto END),0) as saldo")
            ->havingRaw('saldo > 0')
            ->orderByDesc('saldo')->limit($limit)->get();

        return [
            'deuda_total' => round(max(0, $deudaTotal), 2),
            'top_deudores' => $topDeudores->map(fn ($r) => [
                'cliente' => trim($r->nombre),
                'saldo' => round((float) $r->saldo, 2),
            ])->all(),
        ];
    }
}
