<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\ReporteChatSesion;
use App\Services\Ai\ReportesAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportesChatController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'reportes.chat.index');

        $sesiones = ReporteChatSesion::where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->get();

        return view('report.chat.index', compact('sesiones'));
    }

    public function crearSesion()
    {
        Gate::authorize('haveaccess', 'reportes.chat.index');

        $sesion = ReporteChatSesion::create(['user_id' => auth()->id()]);

        return response()->json(['success' => true, 'sesion_id' => $sesion->id]);
    }

    public function historial($sesionId)
    {
        Gate::authorize('haveaccess', 'reportes.chat.index');

        $sesion = ReporteChatSesion::where('user_id', auth()->id())->findOrFail($sesionId);

        return response()->json([
            'success' => true,
            'titulo' => $sesion->titulo,
            'mensajes' => $sesion->mensajes()
                ->whereIn('role', ['user', 'assistant'])
                ->whereNotNull('content')
                ->orderBy('id')
                ->get(['role', 'content'])
                ->values(),
        ]);
    }

    public function enviar(Request $request, $sesionId)
    {
        Gate::authorize('haveaccess', 'reportes.chat.index');

        $request->validate(['pregunta' => 'required|string|max:1000']);

        $sesion = ReporteChatSesion::where('user_id', auth()->id())->findOrFail($sesionId);

        $respuesta = app(ReportesAgentService::class)->responder($sesion, $request->pregunta);

        return response()->json([
            'success' => true,
            'respuesta' => $respuesta->content,
            'titulo' => $sesion->fresh()->titulo,
        ]);
    }
}
