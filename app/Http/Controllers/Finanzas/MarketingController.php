<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\AdSpendDiario;
use App\Models\MetaAdsCampanaInsight;
use App\Models\ecommerce\order_ecommerce;
use App\Services\Ads\GoogleAdsService;
use App\Services\Ads\MetaAdsService;
use App\Services\Finanzas\GastoAdsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    public function sincronizarAhora(MetaAdsService $meta, GoogleAdsService $google, GastoAdsService $gastoAds)
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.sincronizar');

        if (!$meta->habilitado() && !$google->habilitado()) {
            return response()->json(['estado' => 0, 'mensaje' => 'Todavía no hay claves de Meta Ads ni Google Ads cargadas.'], 422);
        }

        $desde = now()->subDays(4);
        $hasta = now();

        $diasMeta = $meta->habilitado() ? $meta->sincronizar($desde, $hasta) : 0;
        if ($meta->habilitado()) {
            $meta->sincronizarPorCampana($desde, $hasta);
            $gastoAds->generarOActualizarGastoMensual('meta', 'Meta');
        }
        $diasGoogle = $google->habilitado() ? $google->sincronizar($desde, $hasta) : 0;
        if ($google->habilitado()) {
            $gastoAds->generarOActualizarGastoMensual('google', 'Google');
        }

        return response()->json([
            'estado' => 1,
            'mensaje' => "Sincronizado: {$diasMeta} día(s) de Meta, {$diasGoogle} día(s) de Google.",
        ]);
    }

    /** Tablero de ROI: cruza gasto por campaña (Meta) con ventas reales por utm_campaign. */
    public function roi(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.index');

        $campanas = MetaAdsCampanaInsight::selectRaw('meta_campaign_id, nombre_campana, SUM(spend) as gasto')
            ->groupBy('meta_campaign_id', 'nombre_campana')
            ->orderByDesc('gasto')
            ->get();

        foreach ($campanas as $c) {
            $ventas = order_ecommerce::where('utm_campaign', $c->nombre_campana)
                ->whereIn('status_order_id', [3, 4, 5]) // Pagado, Enviado, Entregado
                ->get();

            $c->gasto = (float) $c->gasto;
            $c->ventas_totales = (float) $ventas->sum('total_amount');
            $c->cantidad_ventas = $ventas->count();
            $c->roas = $c->gasto > 0 ? round($c->ventas_totales / $c->gasto, 2) : null;
            $c->cac = $c->cantidad_ventas > 0 ? round($c->gasto / $c->cantidad_ventas, 2) : null;
        }

        // Desglose por día del mes seleccionado (?mes=YYYY-MM), por campaña
        $mes = $request->query('mes')
            ? Carbon::createFromFormat('Y-m', $request->query('mes'))->startOfMonth()
            : now()->startOfMonth();

        $insightsDelMes = MetaAdsCampanaInsight::whereBetween('fecha', [$mes->toDateString(), $mes->copy()->endOfMonth()->toDateString()])
            ->orderBy('fecha')
            ->get()
            ->groupBy('meta_campaign_id');

        $campanasDelMes = $insightsDelMes->map(function ($filas) {
            return (object) [
                'meta_campaign_id' => $filas->first()->meta_campaign_id,
                'nombre_campana' => $filas->first()->nombre_campana,
                'gasto_mes' => (float) $filas->sum('spend'),
                'dias' => $filas->map(fn ($f) => ['fecha' => $f->fecha->format('d/m'), 'spend' => (float) $f->spend])->values(),
            ];
        })->sortByDesc('gasto_mes')->values();

        return view('finanzas.marketing.roi', [
            'campanas' => $campanas,
            'mes' => $mes,
            'campanasDelMes' => $campanasDelMes,
        ]);
    }
}
