<?php

namespace App\Services;

use App\Models\Cheque;
use App\Models\Compra;
use App\Models\Notificacion;
use App\Models\RevendedorComision;
use App\Models\Solicitud;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Gate de aprobacion para acciones que salen de lo normal (anulaciones,
 * compra/venta de divisas): un full-access ("superadmin") las ejecuta
 * directo, cualquier otro usuario genera una Solicitud pendiente que no
 * hace nada hasta que un full-access la aprueba desde /admin/solicitudes.
 */
class SolicitudAprobacionService
{
    /**
     * @return array{ejecutado: bool, resultado?: mixed, solicitud?: Solicitud}
     */
    public function ejecutarOSolicitar(string $tipo, string $descripcion, array $datos, ?Model $origen, callable $accion): array
    {
        if (Auth::user()->esSuperAdmin()) {
            return ['ejecutado' => true, 'resultado' => $accion()];
        }

        $solicitud = Solicitud::create([
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'datos' => $datos,
            'origen_type' => $origen ? get_class($origen) : null,
            'origen_id' => $origen?->getKey(),
            'solicitante_id' => Auth::id(),
            'estado' => 'pendiente',
        ]);

        Notificacion::avisar('solicitud',
            'Solicitud de aprobación: ' . $descripcion,
            'Pedida por ' . (Auth::user()->name ?? 'un usuario'),
            url('admin/solicitudes'), 'alerta');

        return ['ejecutado' => false, 'solicitud' => $solicitud];
    }

    /** @throws \RuntimeException si la accion real no se pudo ejecutar (la solicitud queda pendiente) */
    public function aprobar(Solicitud $solicitud, int $aprobadorId): void
    {
        if ($solicitud->estado !== 'pendiente') {
            throw new \RuntimeException('Esta solicitud ya fue resuelta.');
        }

        DB::transaction(function () use ($solicitud) {
            $this->ejecutarAccion($solicitud);
        });

        $solicitud->update([
            'estado' => 'aprobada',
            'aprobado_por' => $aprobadorId,
            'resuelto_at' => now(),
        ]);
    }

    public function rechazar(Solicitud $solicitud, int $aprobadorId, ?string $motivo): void
    {
        if ($solicitud->estado !== 'pendiente') {
            throw new \RuntimeException('Esta solicitud ya fue resuelta.');
        }

        $solicitud->update([
            'estado' => 'rechazada',
            'aprobado_por' => $aprobadorId,
            'resuelto_at' => now(),
            'motivo_rechazo' => $motivo,
        ]);
    }

    private function ejecutarAccion(Solicitud $solicitud): void
    {
        $datos = $solicitud->datos;

        match ($solicitud->tipo) {
            'venta.anular' => $this->anularVenta($datos['idventa']),
            'compra.anular' => $this->anularCompra($datos['idcompra']),
            'cheque.rechazar' => $this->rechazarCheque($datos['cheque_id'], $datos['motivo'] ?? null),
            'cheque.anular' => $this->anularCheque($datos['cheque_id']),
            'divisa.compra' => app(OperacionCambioService::class)->registrarCompra($datos, $solicitud->solicitante_id),
            'divisa.venta' => app(OperacionCambioService::class)->registrarVenta($datos, $solicitud->solicitante_id),
            default => throw new \RuntimeException("Tipo de solicitud desconocido: {$solicitud->tipo}"),
        };
    }

    private function anularVenta(int $idventa): void
    {
        $venta = Venta::findOrFail($idventa);
        $venta->estado = 'anulada';
        $venta->save();

        RevendedorComision::where('venta_id', $idventa)
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->update(['estado' => 'anulada']);
    }

    private function anularCompra(int $idcompra): void
    {
        $compra = Compra::findOrFail($idcompra);
        $compra->estado = 'anulada';
        $compra->save();
    }

    private function rechazarCheque(int $chequeId, ?string $motivo): void
    {
        $cheque = Cheque::findOrFail($chequeId);

        if (in_array($cheque->estado, ['rechazado', 'anulado', 'entregado'])) {
            throw new \RuntimeException('Este cheque ya no se puede rechazar (cambió de estado desde que se pidió la solicitud).');
        }

        $cheque->update([
            'estado' => 'rechazado',
            'observaciones' => trim(($cheque->observaciones ? $cheque->observaciones . ' — ' : '') . 'Rechazado' . ($motivo ? ": {$motivo}" : '')),
        ]);

        Notificacion::avisar('cheque',
            'Cheque Nº ' . ($cheque->numero ?: $cheque->id) . ' rechazado ($' . number_format($cheque->monto, 0, ',', '.') . ')',
            $motivo ?: null,
            url('finanzas/cheques'), 'alerta');
    }

    private function anularCheque(int $chequeId): void
    {
        $cheque = Cheque::findOrFail($chequeId);

        if (in_array($cheque->estado, ['entregado', 'acreditado'])) {
            throw new \RuntimeException('Este cheque ya no se puede anular (cambió de estado desde que se pidió la solicitud).');
        }

        $cheque->update(['estado' => 'anulado']);
    }
}
