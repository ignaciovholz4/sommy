<?php

namespace App\Services\Finanzas;

use App\Models\AdSpendDiario;
use App\Models\Gasto;
use App\Models\GastoCategoria;
use App\Models\MetaAdsConfig;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * Refleja el gasto publicitario del mes como UN "Gasto" pendiente por
 * plataforma (categoria "Publicidad (Ads)"), consolidado de toda la cuenta
 * (no por campana) — asi coincide con como Meta/Google cobran en la
 * realidad, y el usuario lo paga una sola vez desde Finanzas > Gastos con
 * la cuenta/caja que corresponda (Gasto::registrarPago ya existe, no se
 * reinventa el circuito de pago).
 *
 * Idempotente: mientras el Gasto del mes siga "pendiente" se actualiza el
 * monto en cada sincronizacion; una vez pagado no se vuelve a tocar.
 * Usado tanto por el cron diario (SincronizarGastoAds) como por el boton
 * "Sincronizar ahora" del panel.
 */
class GastoAdsService
{
    public function generarOActualizarGastoMensual(string $plataforma, string $nombrePlataforma): void
    {
        $primerDiaMes = now()->startOfMonth()->toDateString();

        $monto = (float) AdSpendDiario::where('plataforma', $plataforma)
            ->where('fecha', '>=', $primerDiaMes)
            ->sum('monto');

        if ($monto <= 0) {
            return;
        }

        $categoria = GastoCategoria::where('nombre', 'Publicidad (Ads)')->first();
        if (!$categoria) {
            Log::warning('GastoAdsService: no se encontro la categoria de gasto "Publicidad (Ads)", no se genera el Gasto de ' . $nombrePlataforma . '.');
            return;
        }

        $descripcion = "{$nombrePlataforma} Ads - gasto de " . now()->translatedFormat('F Y');

        $gasto = Gasto::where('gasto_categoria_id', $categoria->id)
            ->where('fecha', $primerDiaMes)
            ->where('descripcion', $descripcion)
            ->first();

        if ($gasto) {
            if ($gasto->estado === 'pendiente' && (float) $gasto->monto !== $monto) {
                $gasto->update(['monto' => $monto]);
            }
            return;
        }

        $userId = MetaAdsConfig::actual()->actualizado_por
            ?? User::whereHas('roles', fn ($q) => $q->where('full-access', 'yes'))->value('id');

        if (!$userId) {
            Log::warning("GastoAdsService: no se encontro un usuario para asignar el Gasto de {$nombrePlataforma} Ads.");
            return;
        }

        Gasto::create([
            'fecha' => $primerDiaMes,
            'gasto_categoria_id' => $categoria->id,
            'descripcion' => $descripcion,
            'monto' => $monto,
            'user_id' => $userId,
            'estado' => 'pendiente',
        ]);
    }
}
