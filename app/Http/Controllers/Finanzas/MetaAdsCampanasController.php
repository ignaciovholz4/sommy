<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\MetaAdsConfig;
use App\Services\Ads\MetaCampanasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class MetaAdsCampanasController extends Controller
{
    public function index(MetaCampanasService $service)
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.campanas.index');

        return view('finanzas.marketing.campanas.index', [
            'habilitado' => $service->habilitado(),
            'campanas' => $service->listar(),
        ]);
    }

    public function create()
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.campanas.crear');

        return view('finanzas.marketing.campanas.crear');
    }

    public function store(Request $request, MetaCampanasService $service)
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.campanas.crear');

        $validado = $request->validate([
            'nombre' => 'required|string|max:150',
            'objetivo' => 'nullable|string',
            'presupuesto_diario' => 'required|numeric|min:1',
            'texto' => 'nullable|string|max:500',
            'link_destino' => 'nullable|url',
            'imagen' => 'required|image|max:10240',
        ]);

        $validado['imagen_path'] = $request->file('imagen')->store('meta_ads/temp');

        try {
            $resultado = $service->solicitarCrearCampana($validado);
        } catch (\Throwable $th) {
            Storage::delete($validado['imagen_path']);
            return back()->with('error', 'No se pudo crear la campaña: ' . $th->getMessage())->withInput();
        }

        if (!$resultado['ok']) {
            Storage::delete($validado['imagen_path']);
            return back()->with('error', $resultado['mensaje'])->withInput();
        }

        return redirect()->route('finanzas.marketing.campanas.index')->with('success', $resultado['mensaje']);
    }

    public function cambiarEstado(Request $request, string $campaignId, MetaCampanasService $service)
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.campanas.estado');

        $validado = $request->validate([
            'nuevo_estado' => 'required|in:ACTIVE,PAUSED',
            'nombre_campana' => 'nullable|string',
        ]);

        try {
            $resultado = $service->solicitarCambiarEstado($campaignId, $validado['nuevo_estado'], $validado['nombre_campana'] ?? null);
        } catch (\Throwable $th) {
            return response()->json(['estado' => 0, 'mensaje' => $th->getMessage()], 422);
        }

        if (!$resultado['ok']) {
            return response()->json(['estado' => 0, 'mensaje' => $resultado['mensaje']], 422);
        }

        return response()->json(['estado' => 1] + $resultado);
    }

    public function actualizarPresupuesto(Request $request, string $adsetId, MetaCampanasService $service)
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.campanas.presupuesto');

        $validado = $request->validate([
            'presupuesto_diario' => 'required|numeric|min:1',
            'nombre_campana' => 'nullable|string',
        ]);

        try {
            $resultado = $service->solicitarActualizarPresupuesto($adsetId, (float) $validado['presupuesto_diario'], $validado['nombre_campana'] ?? null);
        } catch (\Throwable $th) {
            return response()->json(['estado' => 0, 'mensaje' => $th->getMessage()], 422);
        }

        if (!$resultado['ok']) {
            return response()->json(['estado' => 0, 'mensaje' => $resultado['mensaje']], 422);
        }

        return response()->json(['estado' => 1] + $resultado);
    }

    public function config()
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.config');

        return view('finanzas.marketing.config', ['config' => MetaAdsConfig::actual()]);
    }

    public function guardarConfig(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.marketing.config');

        $validado = $request->validate([
            'tope_presupuesto_diario' => 'nullable|numeric|min:0',
            'tope_presupuesto_total' => 'nullable|numeric|min:0',
            'tope_gasto_diario_cuenta' => 'nullable|numeric|min:0',
        ]);

        $config = MetaAdsConfig::actual();
        $config->update($validado + ['actualizado_por' => Auth::id()]);

        return back()->with('success', 'Topes actualizados.');
    }
}
