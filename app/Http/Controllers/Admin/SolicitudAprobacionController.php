<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Services\SolicitudAprobacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Lista de solicitudes de aprobacion pendientes (anulaciones, compra/venta
 * de divisas) generadas por App\Services\SolicitudAprobacionService cuando
 * un usuario que no es full-access intenta hacer una de esas acciones.
 */
class SolicitudAprobacionController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'admin.solicitudes.index');

        return view('admin.solicitudes.index');
    }

    public function data(Request $request)
    {
        Gate::authorize('haveaccess', 'admin.solicitudes.index');

        $estado = $request->query('estado', 'pendiente');

        $solicitudes = Solicitud::with(['solicitante', 'aprobadoPor'])
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'estado' => 1,
            'data' => $solicitudes->map(fn (Solicitud $s) => [
                'id' => $s->id,
                'tipo' => $s->tipo,
                'descripcion' => $s->descripcion,
                'solicitante' => optional($s->solicitante)->name ?? '—',
                'fecha' => $s->created_at->format('d/m/Y H:i'),
                'estado' => $s->estado,
                'aprobado_por' => optional($s->aprobadoPor)->name,
                'resuelto_at' => optional($s->resuelto_at)->format('d/m/Y H:i'),
                'motivo_rechazo' => $s->motivo_rechazo,
                'origen_url' => $this->urlOrigen($s),
            ])->values(),
        ]);
    }

    public function aprobar(Solicitud $solicitud, SolicitudAprobacionService $solicitudes)
    {
        Gate::authorize('haveaccess', 'admin.solicitudes.manage');

        try {
            $solicitudes->aprobar($solicitud, Auth::id());
        } catch (\Throwable $e) {
            return response()->json(['estado' => 0, 'mensaje' => $e->getMessage()], 422);
        }

        return response()->json(['estado' => 1, 'mensaje' => 'Solicitud aprobada y ejecutada.']);
    }

    public function rechazar(Request $request, Solicitud $solicitud, SolicitudAprobacionService $solicitudes)
    {
        Gate::authorize('haveaccess', 'admin.solicitudes.manage');

        try {
            $solicitudes->rechazar($solicitud, Auth::id(), $request->input('motivo'));
        } catch (\Throwable $e) {
            return response()->json(['estado' => 0, 'mensaje' => $e->getMessage()], 422);
        }

        return response()->json(['estado' => 1, 'mensaje' => 'Solicitud rechazada.']);
    }

    private function urlOrigen(Solicitud $s): ?string
    {
        return match ($s->tipo) {
            'venta.anular' => url('ventas?ver=' . ($s->datos['idventa'] ?? '')),
            'compra.anular' => url('compras?ver=' . ($s->datos['idcompra'] ?? '')),
            'cheque.rechazar', 'cheque.anular' => url('finanzas/cheques'),
            'divisa.compra', 'divisa.venta' => url('finanzas/divisas'),
            default => null,
        };
    }
}
