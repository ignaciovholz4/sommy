<?php

namespace App\Services;

use App\Models\Movimiento;
use App\Models\OperacionCambio;
use App\Models\OperacionCambioConsumo;
use Illuminate\Support\Facades\DB;

/**
 * Compra/venta de moneda extranjera (USD, USDT) contra pesos, con costeo
 * FIFO: cada compra es un lote con su cotizacion; cada venta consume los
 * lotes mas viejos primero y calcula la ganancia/perdida realizada contra
 * ese costo. Crea los Movimiento de tesoreria (egreso/ingreso) igual que
 * cualquier otro medio de pago, no reemplaza esa contabilidad.
 */
class OperacionCambioService
{
    /**
     * Compra de moneda extranjera: sale ARS de $cuentaArs, entra la moneda a
     * $cuentaMoneda. Crea un lote nuevo disponible para futuras ventas.
     */
    public function registrarCompra(array $datos, ?int $usuarioId): OperacionCambio
    {
        return DB::transaction(function () use ($datos, $usuarioId) {
            $montoMoneda = round((float) $datos['monto_moneda'], 2);
            $cotizacion  = (float) $datos['cotizacion'];
            $montoArs    = round($montoMoneda * $cotizacion, 2);
            $fecha       = $datos['fecha'] ?? now();

            $comprobante = 'CAMBIO-' . uniqid();

            $movArs = Movimiento::create([
                'cuenta_id' => $datos['cuenta_ars_id'],
                'fecha' => $fecha,
                'tipo' => 'egreso',
                'medio' => 'cambio_divisa',
                'cliente_proveedor' => 'Compra de moneda extranjera',
                'comprobante' => $comprobante,
                'observaciones' => 'Compra de moneda extranjera' . (($datos['observaciones'] ?? null) ? ': ' . $datos['observaciones'] : ''),
                'bancos' => $montoArs,
                'total' => $montoArs,
            ]);

            $movMoneda = Movimiento::create([
                'cuenta_id' => $datos['cuenta_moneda_id'],
                'fecha' => $fecha,
                'tipo' => 'ingreso',
                'medio' => 'cambio_divisa',
                'cliente_proveedor' => 'Compra de moneda extranjera',
                'comprobante' => $comprobante,
                'observaciones' => 'Compra de moneda extranjera' . (($datos['observaciones'] ?? null) ? ': ' . $datos['observaciones'] : ''),
                'bancos' => $montoMoneda,
                'total' => $montoMoneda,
            ]);

            return OperacionCambio::create([
                'tipo' => 'compra',
                'moneda_id' => $datos['moneda_id'],
                'cuenta_ars_id' => $datos['cuenta_ars_id'],
                'cuenta_moneda_id' => $datos['cuenta_moneda_id'],
                'monto_moneda' => $montoMoneda,
                'cotizacion' => $cotizacion,
                'monto_ars' => $montoArs,
                'fecha' => $fecha,
                'observaciones' => $datos['observaciones'] ?? null,
                'movimiento_ars_id' => $movArs->id,
                'movimiento_moneda_id' => $movMoneda->id,
                'disponible' => $montoMoneda,
                'creado_por' => $usuarioId,
            ]);
        });
    }

    /**
     * Venta de moneda extranjera: sale la moneda de $cuentaMoneda, entra ARS
     * a $cuentaArs. Consume lotes de compra FIFO y calcula el resultado
     * realizado (monto_ars recibido - costo de los lotes consumidos).
     *
     * @throws \RuntimeException si no hay suficiente moneda disponible
     */
    public function registrarVenta(array $datos, ?int $usuarioId): OperacionCambio
    {
        return DB::transaction(function () use ($datos, $usuarioId) {
            $monedaId = $datos['moneda_id'];
            $aVender  = round((float) $datos['monto_moneda'], 2);

            $lotes = OperacionCambio::where('moneda_id', $monedaId)
                ->where('tipo', 'compra')
                ->where('disponible', '>', 0)
                ->orderBy('fecha')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $totalDisponible = (float) $lotes->sum('disponible');
            if ($aVender > $totalDisponible + 0.009) {
                throw new \RuntimeException(
                    'No hay suficiente moneda comprada registrada (disponible: ' . number_format($totalDisponible, 2, ',', '.') . ').'
                );
            }

            $cotizacion = (float) $datos['cotizacion'];
            $montoArs   = round($aVender * $cotizacion, 2);
            $fecha      = $datos['fecha'] ?? now();
            $comprobante = 'CAMBIO-' . uniqid();

            $movMoneda = Movimiento::create([
                'cuenta_id' => $datos['cuenta_moneda_id'],
                'fecha' => $fecha,
                'tipo' => 'egreso',
                'medio' => 'cambio_divisa',
                'cliente_proveedor' => 'Venta de moneda extranjera',
                'comprobante' => $comprobante,
                'observaciones' => 'Venta de moneda extranjera' . (($datos['observaciones'] ?? null) ? ': ' . $datos['observaciones'] : ''),
                'bancos' => $aVender,
                'total' => $aVender,
            ]);

            $movArs = Movimiento::create([
                'cuenta_id' => $datos['cuenta_ars_id'],
                'fecha' => $fecha,
                'tipo' => 'ingreso',
                'medio' => 'cambio_divisa',
                'cliente_proveedor' => 'Venta de moneda extranjera',
                'comprobante' => $comprobante,
                'observaciones' => 'Venta de moneda extranjera' . (($datos['observaciones'] ?? null) ? ': ' . $datos['observaciones'] : ''),
                'bancos' => $montoArs,
                'total' => $montoArs,
            ]);

            $restante = $aVender;
            $costoTotal = 0.0;
            $consumos = [];

            foreach ($lotes as $lote) {
                if ($restante <= 0) {
                    break;
                }

                $tomar = min((float) $lote->disponible, $restante);
                $costo = round($tomar * (float) $lote->cotizacion, 2);
                $costoTotal += $costo;

                $lote->update(['disponible' => round((float) $lote->disponible - $tomar, 2)]);

                $consumos[] = ['compra_id' => $lote->id, 'cantidad' => $tomar, 'costo_ars' => $costo];
                $restante = round($restante - $tomar, 2);
            }

            $venta = OperacionCambio::create([
                'tipo' => 'venta',
                'moneda_id' => $monedaId,
                'cuenta_ars_id' => $datos['cuenta_ars_id'],
                'cuenta_moneda_id' => $datos['cuenta_moneda_id'],
                'monto_moneda' => $aVender,
                'cotizacion' => $cotizacion,
                'monto_ars' => $montoArs,
                'fecha' => $fecha,
                'observaciones' => $datos['observaciones'] ?? null,
                'movimiento_ars_id' => $movArs->id,
                'movimiento_moneda_id' => $movMoneda->id,
                'resultado' => round($montoArs - $costoTotal, 2),
                'creado_por' => $usuarioId,
            ]);

            foreach ($consumos as $c) {
                OperacionCambioConsumo::create(array_merge($c, ['venta_id' => $venta->id]));
            }

            return $venta;
        });
    }

    /**
     * Un cliente pagó una venta en moneda extranjera: entran dólares a la
     * cuenta (ya se registró como Movimiento aparte), pero no hay una cuenta
     * ARS real de por medio — los pesos "entran" saldando la venta, no a una
     * caja. Se registra igual como un lote de compra nuevo (mismo costeo
     * FIFO que si se hubiese comprado la divisa), para que quede el registro
     * de a qué cotización se tomó y para que después se pueda gastar/vender.
     */
    public function registrarIngresoPorCobro(array $datos, ?int $usuarioId): OperacionCambio
    {
        $montoMoneda = round((float) $datos['monto_moneda'], 2);
        $cotizacion = (float) $datos['cotizacion'];
        $montoArs = round($montoMoneda * $cotizacion, 2);

        return OperacionCambio::create([
            'tipo' => 'compra',
            'moneda_id' => $datos['moneda_id'],
            'cuenta_ars_id' => null,
            'cuenta_moneda_id' => $datos['cuenta_moneda_id'],
            'monto_moneda' => $montoMoneda,
            'cotizacion' => $cotizacion,
            'monto_ars' => $montoArs,
            'fecha' => $datos['fecha'] ?? now(),
            'observaciones' => $datos['observaciones'] ?? null,
            'movimiento_ars_id' => null,
            'movimiento_moneda_id' => $datos['movimiento_id'] ?? null,
            'disponible' => $montoMoneda,
            'referencia_type' => $datos['referencia_type'] ?? null,
            'referencia_id' => $datos['referencia_id'] ?? null,
            'creado_por' => $usuarioId,
        ]);
    }

    /**
     * Se pagó una compra en moneda extranjera: salen dólares de la cuenta
     * (ya registrado como Movimiento aparte). Consume lotes FIFO existentes
     * igual que una venta de divisa, pero sin cuenta ARS real. A diferencia
     * de registrarVenta(), nunca falla por falta de disponible: si no hay
     * lotes (o no alcanzan), lo que falte queda sin costo conocido —
     * preferible a bloquear el pago de la compra por un dato de tesorería
     * secundario.
     */
    public function registrarConsumoPorPago(array $datos, ?int $usuarioId): OperacionCambio
    {
        return DB::transaction(function () use ($datos, $usuarioId) {
            $monedaId = $datos['moneda_id'];
            $aGastar = round((float) $datos['monto_moneda'], 2);
            $cotizacion = (float) $datos['cotizacion'];
            $montoArs = round($aGastar * $cotizacion, 2);
            $fecha = $datos['fecha'] ?? now();

            $lotes = OperacionCambio::where('moneda_id', $monedaId)
                ->where('tipo', 'compra')
                ->where('disponible', '>', 0)
                ->orderBy('fecha')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $restante = $aGastar;
            $costoTotal = 0.0;
            $consumos = [];

            foreach ($lotes as $lote) {
                if ($restante <= 0) {
                    break;
                }

                $tomar = min((float) $lote->disponible, $restante);
                $costo = round($tomar * (float) $lote->cotizacion, 2);
                $costoTotal += $costo;

                $lote->update(['disponible' => round((float) $lote->disponible - $tomar, 2)]);

                $consumos[] = ['compra_id' => $lote->id, 'cantidad' => $tomar, 'costo_ars' => $costo];
                $restante = round($restante - $tomar, 2);
            }
            // Lo que no cubrieron los lotes (no había suficiente comprado registrado)
            // queda sin costo conocido: el resultado de esa porción no se calcula.

            $consumo = OperacionCambio::create([
                'tipo' => 'venta',
                'moneda_id' => $monedaId,
                'cuenta_ars_id' => null,
                'cuenta_moneda_id' => $datos['cuenta_moneda_id'],
                'monto_moneda' => $aGastar,
                'cotizacion' => $cotizacion,
                'monto_ars' => $montoArs,
                'fecha' => $fecha,
                'observaciones' => $datos['observaciones'] ?? null,
                'movimiento_ars_id' => null,
                'movimiento_moneda_id' => $datos['movimiento_id'] ?? null,
                'resultado' => $restante > 0 ? null : round($montoArs - $costoTotal, 2),
                'referencia_type' => $datos['referencia_type'] ?? null,
                'referencia_id' => $datos['referencia_id'] ?? null,
                'creado_por' => $usuarioId,
            ]);

            foreach ($consumos as $c) {
                OperacionCambioConsumo::create(array_merge($c, ['venta_id' => $consumo->id]));
            }

            return $consumo;
        });
    }

    /** Cantidad disponible y costo promedio ponderado actual de una moneda, para mostrar antes de vender. */
    public function disponible(int $monedaId): array
    {
        $lotes = OperacionCambio::where('moneda_id', $monedaId)
            ->where('tipo', 'compra')
            ->where('disponible', '>', 0)
            ->get();

        $cantidad = (float) $lotes->sum('disponible');
        $costoTotal = (float) $lotes->sum(fn ($l) => $l->disponible * $l->cotizacion);

        return [
            'cantidad' => round($cantidad, 2),
            'costo_promedio' => $cantidad > 0 ? round($costoTotal / $cantidad, 4) : 0,
        ];
    }
}
