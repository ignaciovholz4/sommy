<?php

namespace App\Services\Cobranzas;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Deuda vencida de clientes: combina cargos de cuenta corriente vencidos
 * (cliente_cc_movimientos.fecha_vencimiento) y ventas "a cobrar" cuyo plazo
 * (fecha + condicion_pago_dias del cliente) ya paso.
 *
 * OJO: a diferencia de proveedor_cc_movimientos, aca no hay reimputacion
 * FIFO que ligue cada pago a un cargo puntual, asi que "vencido" es una
 * aproximacion: se usa el saldo total de cuenta corriente (cargos-pagos) del
 * cliente cuando existe AL MENOS un cargo vencido, no el monto exacto del
 * cargo vencido. Los movimientos historicos sin fecha_vencimiento (previos a
 * esta migracion) nunca se consideran vencidos.
 */
class DeudaVencidaService
{
    /** @return Collection<int, array{cliente_id: int, monto_vencido: float, dias_vencido_max: int}> */
    public function calcular(): Collection
    {
        $hoy = now()->toDateString();

        // Saldo total de cuenta corriente por cliente (cargos - pagos)
        $saldosCC = DB::table('cliente_cc_movimientos')
            ->groupBy('cliente_id')
            ->selectRaw("cliente_id, SUM(CASE WHEN tipo='cargo' THEN monto ELSE -monto END) as saldo")
            ->havingRaw('saldo > 0')
            ->get()->keyBy('cliente_id');

        // Clientes con al menos un cargo vencido: nos quedamos con el vencimiento mas antiguo
        $vencidoCC = DB::table('cliente_cc_movimientos')
            ->where('tipo', 'cargo')
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', $hoy)
            ->where('estado', '!=', 'pagado')
            ->groupBy('cliente_id')
            ->selectRaw('cliente_id, MIN(fecha_vencimiento) as vencimiento')
            ->get()->keyBy('cliente_id');

        // Ventas "a cobrar" vencidas (plazo propio del cliente o el default general)
        $default = (int) config('services.cobranzas.dias_gracia_default', 30);
        $ventasVencidas = DB::table('ventas as v')
            ->join('clientes as c', 'c.idcliente', '=', 'v.cliente_id')
            ->leftJoin(DB::raw('(SELECT comprobante, SUM(total) as cobrado FROM movimientos GROUP BY comprobante) mv'), 'mv.comprobante', '=', 'v.num_folio')
            ->where('v.estado', 'a cobrar')
            ->selectRaw('v.cliente_id, v.fecha, c.condicion_pago_dias, (v.total_con_iva - COALESCE(mv.cobrado, 0)) as pendiente')
            ->get()
            ->filter(fn ($r) => (float) $r->pendiente > 0.009)
            ->map(function ($r) use ($default) {
                $r->vencimiento = \Carbon\Carbon::parse($r->fecha)->addDays((int) ($r->condicion_pago_dias ?? $default))->toDateString();
                return $r;
            })
            ->filter(fn ($r) => $r->vencimiento < now()->toDateString())
            ->groupBy('cliente_id');

        $porCliente = [];

        foreach ($vencidoCC as $clienteId => $row) {
            $saldo = (float) ($saldosCC[$clienteId]->saldo ?? 0);
            if ($saldo <= 0) {
                continue;
            }
            $porCliente[$clienteId] = [
                'cliente_id' => $clienteId,
                'monto_vencido' => $saldo,
                'dias_vencido_max' => now()->diffInDays($row->vencimiento),
            ];
        }

        foreach ($ventasVencidas as $clienteId => $ventas) {
            $pendienteVencido = (float) $ventas->sum('pendiente');
            $vencimientoMasAntiguo = $ventas->min('vencimiento');
            $dias = now()->diffInDays($vencimientoMasAntiguo);

            if (isset($porCliente[$clienteId])) {
                $porCliente[$clienteId]['monto_vencido'] += $pendienteVencido;
                $porCliente[$clienteId]['dias_vencido_max'] = max($porCliente[$clienteId]['dias_vencido_max'], $dias);
            } else {
                $porCliente[$clienteId] = [
                    'cliente_id' => $clienteId,
                    'monto_vencido' => $pendienteVencido,
                    'dias_vencido_max' => $dias,
                ];
            }
        }

        return collect(array_values($porCliente));
    }
}
