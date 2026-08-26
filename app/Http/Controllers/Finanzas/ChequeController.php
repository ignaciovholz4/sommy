<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Cheque;
use App\Models\Notificacion;
use App\Services\SolicitudAprobacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Cartera de cheques: libro subsidiario de custodia y seguimiento de los
 * cheques propios y de terceros que se registran como medio de pago en
 * Ventas, Pedidos, Compras, Gastos y CxP (ver App\Services\ChequeService).
 */
class ChequeController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'finanzas.cheques.index');

        return view('finanzas.cheques.index');
    }

    public function data(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.cheques.index');

        $tipo   = $request->query('tipo', 'todos');
        $estado = $request->query('estado', 'en_cartera');

        $cheques = Cheque::with('cuenta')
            ->when($tipo !== 'todos', fn ($q) => $q->where('tipo', $tipo))
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->orderBy('fecha_cobro')
            ->get();

        return response()->json([
            'estado' => 1,
            'data' => $cheques->map(fn (Cheque $c) => [
                'id' => $c->id,
                'tipo' => $c->tipo,
                'numero' => $c->numero,
                'banco_emisor' => $c->banco_emisor,
                'contraparte_nombre' => $c->contraparte_nombre,
                'monto' => (float) $c->monto,
                'fecha_emision' => optional($c->fecha_emision)->format('d/m/Y'),
                'fecha_cobro' => $c->fecha_cobro->format('d/m/Y'),
                'estado' => $c->estado,
                'vencido' => $c->estaVencido(),
                'cuenta' => optional($c->cuenta)->nombre,
                'observaciones' => $c->observaciones,
            ])->values(),
        ]);
    }

    /** Cheques de terceros disponibles para endosar, consumido por Compras/Gastos/CxP. */
    public function disponibles()
    {
        $cheques = Cheque::where('tipo', 'tercero')->where('estado', 'en_cartera')
            ->orderBy('fecha_cobro')->get();

        return response()->json([
            'estado' => 1,
            'data' => $cheques->map(fn (Cheque $c) => [
                'id' => $c->id,
                'monto' => (float) $c->monto,
                'label' => 'Cheque Nº ' . ($c->numero ?: '—') . ' · ' . ($c->banco_emisor ?: 'banco s/d')
                    . ' · $' . number_format($c->monto, 2, ',', '.') . ' · vence ' . $c->fecha_cobro->format('d/m/Y')
                    . ($c->contraparte_nombre ? ' · de ' . $c->contraparte_nombre : ''),
            ])->values(),
        ]);
    }

    public function depositar(Request $request, Cheque $cheque)
    {
        Gate::authorize('haveaccess', 'finanzas.cheques.depositar');

        $request->validate(['cuenta_id' => 'required|exists:cuentas,id']);

        if ($cheque->tipo !== 'tercero' || $cheque->estado !== 'en_cartera') {
            return response()->json(['estado' => 0, 'mensaje' => 'Solo se pueden depositar cheques de terceros que estén en cartera.'], 422);
        }

        $cheque->update(['estado' => 'depositado', 'cuenta_id' => $request->cuenta_id]);

        return response()->json(['estado' => 1, 'mensaje' => 'Cheque marcado como depositado.']);
    }

    public function acreditar(Cheque $cheque)
    {
        Gate::authorize('haveaccess', 'finanzas.cheques.acreditar');

        if (!in_array($cheque->estado, ['en_cartera', 'depositado'])) {
            return response()->json(['estado' => 0, 'mensaje' => 'Este cheque no está en un estado que se pueda acreditar.'], 422);
        }

        $cheque->update(['estado' => 'acreditado']);

        return response()->json(['estado' => 1, 'mensaje' => 'Cheque acreditado.']);
    }

    public function rechazar(Request $request, Cheque $cheque, SolicitudAprobacionService $solicitudes)
    {
        Gate::authorize('haveaccess', 'finanzas.cheques.rechazar');

        if (in_array($cheque->estado, ['rechazado', 'anulado', 'entregado'])) {
            return response()->json(['estado' => 0, 'mensaje' => 'Este cheque ya no se puede rechazar.'], 422);
        }

        $motivo = trim((string) $request->input('motivo', ''));

        $resultado = $solicitudes->ejecutarOSolicitar(
            'cheque.rechazar',
            'Rechazar cheque Nº ' . ($cheque->numero ?: $cheque->id) . ' ($' . number_format($cheque->monto, 0, ',', '.') . ')',
            ['cheque_id' => $cheque->id, 'motivo' => $motivo ?: null],
            $cheque,
            function () use ($cheque, $motivo) {
                $cheque->update([
                    'estado' => 'rechazado',
                    'observaciones' => trim(($cheque->observaciones ? $cheque->observaciones . ' — ' : '') . 'Rechazado' . ($motivo ? ": {$motivo}" : '')),
                ]);

                Notificacion::avisar('cheque',
                    'Cheque Nº ' . ($cheque->numero ?: $cheque->id) . ' rechazado ($' . number_format($cheque->monto, 0, ',', '.') . ')',
                    $motivo ?: null,
                    url('finanzas/cheques'), 'alerta');
            }
        );

        if (!$resultado['ejecutado']) {
            return response()->json(['estado' => 1, 'pendiente' => true, 'mensaje' => 'Tu solicitud de rechazo quedó pendiente de aprobación del administrador.']);
        }

        return response()->json(['estado' => 1, 'mensaje' => 'Cheque marcado como rechazado.']);
    }

    public function anular(Cheque $cheque, SolicitudAprobacionService $solicitudes)
    {
        Gate::authorize('haveaccess', 'finanzas.cheques.anular');

        if (in_array($cheque->estado, ['entregado', 'acreditado'])) {
            return response()->json(['estado' => 0, 'mensaje' => 'Un cheque entregado o acreditado no se puede anular.'], 422);
        }

        $resultado = $solicitudes->ejecutarOSolicitar(
            'cheque.anular',
            'Anular cheque Nº ' . ($cheque->numero ?: $cheque->id) . ' ($' . number_format($cheque->monto, 0, ',', '.') . ')',
            ['cheque_id' => $cheque->id],
            $cheque,
            fn () => $cheque->update(['estado' => 'anulado'])
        );

        if (!$resultado['ejecutado']) {
            return response()->json(['estado' => 1, 'pendiente' => true, 'mensaje' => 'Tu solicitud de anulación quedó pendiente de aprobación del administrador.']);
        }

        return response()->json(['estado' => 1, 'mensaje' => 'Cheque anulado.']);
    }
}
