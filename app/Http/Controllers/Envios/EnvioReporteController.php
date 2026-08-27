<?php

namespace App\Http\Controllers\Envios;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Reporte imprimible de entregas a clientes en un periodo: cliente, direccion,
 * cobros (con medio de pago) y las fotos que dejo el fletero en la puerta.
 */
class EnvioReporteController extends Controller
{
    public function pdf(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.envios.index');

        $desde = $request->filled('desde') ? Carbon::parse($request->input('desde'))->startOfDay() : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->input('hasta'))->endOfDay() : Carbon::now()->endOfDay();
        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        $envios = Envio::with(['orden.cliente', 'venta.cliente', 'transportista'])
            ->whereIn('tipo', ['venta', 'venta_manual'])
            ->where('estado', 'entregado')
            ->whereBetween('fecha_entrega_real', [$desde, $hasta])
            ->orderBy('fecha_entrega_real')
            ->get();

        $entregas = DB::table('entregas_fletero')
            ->whereIn('envio_id', $envios->pluck('id'))
            ->get()->keyBy('envio_id');

        $filas = $envios->map(function ($e) use ($entregas) {
            $cliente = optional($e->orden)->cliente ?? optional($e->venta)->cliente;

            $direccion = $e->direccion_entrega ?: trim(implode(', ', array_filter([
                optional($cliente)->direccion,
                optional($cliente)->localidad ?? optional($e->orden)->direccion_localidad,
                optional($cliente)->provincia ?? optional($e->orden)->direccion_provincia,
            ])));

            $referencia = $e->order_ecommerce_id ? 'Pedido #' . $e->order_ecommerce_id
                : (optional($e->venta)->num_folio ?: 'Venta #' . $e->venta_id);

            $comprobante = $e->order_ecommerce_id ? 'Pedido #' . $e->order_ecommerce_id : optional($e->venta)->num_folio;
            $movimientos = $comprobante
                ? DB::table('movimientos')->where('comprobante', $comprobante)->get()
                : collect();

            $total = $e->order_ecommerce_id ? (float) optional($e->orden)->total_amount : (float) optional($e->venta)->total_con_iva;
            $pagado = (float) $movimientos->sum('total_ars');

            $entrega = $entregas->get($e->id);

            return [
                'envio' => $e,
                'cliente_nombre' => trim(optional($cliente)->nombre . ' ' . (optional($cliente)->paterno ?? '')) ?: 'Cliente',
                'cliente_telefono' => optional($cliente)->telefono,
                'cliente_dni' => optional($cliente)->dni_cuit,
                'direccion' => $direccion ?: '—',
                'referencia' => $referencia,
                'fletero' => optional($e->transportista)->nombre ?: '—',
                'fecha_entrega' => $e->fecha_entrega_real,
                'total' => $total,
                'pagado' => $pagado,
                'saldo' => max($total - $pagado, 0),
                'medios' => $movimientos->pluck('medio')->filter()->unique()->values()->implode(', '),
                'cobrado_puerta' => $entrega ? (float) $entrega->monto_cobrado : null,
                'nota_entrega' => $entrega?->nota,
                'fotos' => array_filter([
                    'Firma del cliente' => $this->rutaLocal($entrega?->firma_path),
                    'Foto del efectivo' => $this->rutaLocal($entrega?->foto_plata_path),
                    'Foto de la entrega' => $this->rutaLocal($entrega?->foto_entrega_path),
                ]),
            ];
        });

        $pdf = Pdf::loadView('envios.reporte-fletes-pdf', [
            'filas' => $filas,
            'desde' => $desde,
            'hasta' => $hasta,
            'logo' => public_path('imagenes/marca/sommy-logo.png'),
            'totalCobrado' => $filas->sum('pagado') + $filas->sum(fn ($f) => $f['cobrado_puerta'] ?? 0),
            'totalPendiente' => $filas->sum('saldo'),
        ])->setPaper('a4');

        return $pdf->stream('fletes-' . $desde->format('Y-m-d') . '-a-' . $hasta->format('Y-m-d') . '.pdf');
    }

    protected function rutaLocal(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        $full = public_path($path);
        return is_file($full) ? $full : null;
    }
}
