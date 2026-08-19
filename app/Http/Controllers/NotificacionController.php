<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;

/** Centro de notificaciones: campanita del header + historial completo. */
class NotificacionController extends Controller
{
    /** Historial completo. */
    public function index()
    {
        $notificaciones = Notificacion::orderByDesc('id')->limit(200)->get();

        return view('notificaciones.index', compact('notificaciones'));
    }

    /** Feed para la campanita (se consulta cada ~45 seg). */
    public function feed()
    {
        return response()->json([
            'no_leidas' => Notificacion::whereNull('leida_at')->count(),
            'ultimas' => Notificacion::orderByDesc('id')->limit(12)->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'icono' => Notificacion::ICONOS[$n->tipo] ?? '🔔',
                    'titulo' => $n->titulo,
                    'mensaje' => $n->mensaje,
                    'nivel' => $n->nivel,
                    'leida' => (bool) $n->leida_at,
                    'hace' => $n->created_at->locale('es')->diffForHumans(),
                    'ir' => url('notificaciones/' . $n->id . '/ir'),
                ]),
        ]);
    }

    /** Abre la notificación: la marca leída y redirige a su link. */
    public function ir($id)
    {
        $n = Notificacion::findOrFail($id);
        if (!$n->leida_at) {
            $n->update(['leida_at' => now()]);
        }

        return redirect($n->url ?: url('notificaciones'));
    }

    public function marcarLeidas()
    {
        Notificacion::whereNull('leida_at')->update(['leida_at' => now()]);

        return response()->json(['status' => 1]);
    }
}
