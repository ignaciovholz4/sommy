<?php

namespace App\Http\Controllers;

use App\Services\ClarityService;
use Illuminate\Support\Facades\Gate;

class ClarityController extends Controller
{
    public function index(ClarityService $clarity)
    {
        Gate::authorize('haveaccess', 'clarity.index');

        return view('clarity.index', [
            'habilitado' => $clarity->habilitado(),
            'snapshot' => $clarity->ultimoSnapshot(),
            'projectId' => config('services.clarity.project_id'),
        ]);
    }

    public function sincronizarAhora(ClarityService $clarity)
    {
        Gate::authorize('haveaccess', 'clarity.sincronizar');

        if (!$clarity->habilitado()) {
            return response()->json(['estado' => 0, 'mensaje' => 'Todavía no cargaste el API Token de Clarity en Integraciones.'], 422);
        }

        if (!$clarity->puedeSincronizar()) {
            return response()->json(['estado' => 0, 'mensaje' => 'Ya se sincronizó hace poco — la API de Clarity limita a 10 llamadas por día, probá de nuevo más tarde.'], 422);
        }

        $ok = $clarity->sincronizar();

        return response()->json([
            'estado' => $ok ? 1 : 0,
            'mensaje' => $ok ? 'Sincronizado.' : 'No se pudo sincronizar, revisá el token o los logs.',
        ]);
    }
}
