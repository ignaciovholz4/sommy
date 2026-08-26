<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\AdSpendDiario;
use App\Services\Ads\GoogleAdsService;
use App\Services\Ads\MetaAdsService;
use Illuminate\Support\Facades\Gate;

/**
 * Panel de gasto publicitario (Meta Ads / Google Ads), gasto total por dia.
 * Ver App\Services\Ads\MetaAdsService / GoogleAdsService para la sincronizacion.
 */
class MarketingController extends Controller
{
    public function index(MetaAdsService $meta, GoogleAdsService $google)
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.index');

        return view('finanzas.marketing.index', [
            'metaHabilitado' => $meta->habilitado(),
            'googleHabilitado' => $google->habilitado(),
        ]);
    }

    public function data()
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.index');

        $desde = now()->subDays(29)->startOfDay();
        $inicioMes = now()->startOfMonth();

        $registros = AdSpendDiario::where('fecha', '>=', $desde)->orderBy('fecha')->get();

        $dias = [];
        for ($i = 0; $i < 30; $i++) {
            $dias[] = $desde->copy()->addDays($i)->format('Y-m-d');
        }

        $serieMeta = [];
        $serieGoogle = [];
        foreach ($dias as $fecha) {
            $serieMeta[] = (float) $registros->where('plataforma', 'meta')->where('fecha', $fecha)->sum('monto');
            $serieGoogle[] = (float) $registros->where('plataforma', 'google')->where('fecha', $fecha)->sum('monto');
        }

        $delMes = AdSpendDiario::where('fecha', '>=', $inicioMes)->get();
        $totalMetaMes = (float) $delMes->where('plataforma', 'meta')->sum('monto');
        $totalGoogleMes = (float) $delMes->where('plataforma', 'google')->sum('monto');

        return response()->json([
            'estado' => 1,
            'dias' => array_map(fn ($d) => \Carbon\Carbon::parse($d)->format('d/m'), $dias),
            'serie_meta' => $serieMeta,
            'serie_google' => $serieGoogle,
            'total_meta_mes' => $totalMetaMes,
            'total_google_mes' => $totalGoogleMes,
            'total_combinado_mes' => round($totalMetaMes + $totalGoogleMes, 2),
        ]);
    }

    public function sincronizarAhora(MetaAdsService $meta, GoogleAdsService $google)
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.index');

        if (!$meta->habilitado() && !$google->habilitado()) {
            return response()->json(['estado' => 0, 'mensaje' => 'Todavía no hay claves de Meta Ads ni Google Ads cargadas.'], 422);
        }

        $desde = now()->subDays(4);
        $hasta = now();

        $diasMeta = $meta->habilitado() ? $meta->sincronizar($desde, $hasta) : 0;
        $diasGoogle = $google->habilitado() ? $google->sincronizar($desde, $hasta) : 0;

        return response()->json([
            'estado' => 1,
            'mensaje' => "Sincronizado: {$diasMeta} día(s) de Meta, {$diasGoogle} día(s) de Google.",
        ]);
    }
}
