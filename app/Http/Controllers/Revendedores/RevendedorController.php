<?php

namespace App\Http\Controllers\Revendedores;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\RevendedorPublicController;
use App\Models\Revendedor;
use App\Models\RevendedorComision;
use App\Models\RevendedorPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevendedorController extends Controller
{
    /** Listado con lo único que importa: cuánto vendió cada uno y cuánto le debo */
    public function index(Request $request)
    {
        $desde = $request->input('desde', now()->startOfYear()->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());
        $estado = $request->input('estado', '');
        $buscar = $request->input('q', '');

        $query = Revendedor::query();

        if ($estado !== '') {
            $query->where('estado', $estado);
        }
        if ($buscar !== '') {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        $revendedores = $query->orderBy('nombre')->get();

        // Totales por revendedor dentro del período elegido
        $porRevendedor = RevendedorComision::selectRaw('
                revendedor_id,
                COUNT(*) as ventas,
                SUM(monto_venta) as facturado,
                SUM(CASE WHEN estado IN ("pendiente","aprobada") THEN comision ELSE 0 END) as a_pagar,
                SUM(CASE WHEN estado = "pagada" THEN comision ELSE 0 END) as pagado,
                SUM(CASE WHEN estado <> "anulada" THEN comision ELSE 0 END) as comision_total
            ')
            ->whereBetween(DB::raw('DATE(created_at)'), [$desde, $hasta])
            ->groupBy('revendedor_id')
            ->get()
            ->keyBy('revendedor_id');

        // Deuda total viva (sin filtro de fecha: lo que debo es lo que debo)
        $deudaViva = RevendedorComision::whereIn('estado', ['pendiente', 'aprobada'])
            ->selectRaw('revendedor_id, SUM(comision) as total')
            ->groupBy('revendedor_id')
            ->pluck('total', 'revendedor_id');

        $resumen = [
            'revendedores'   => $revendedores->count(),
            'activos'        => $revendedores->where('estado', 'activo')->count(),
            'pendientes'     => $revendedores->where('estado', 'pendiente')->count(),
            'ventas'         => $porRevendedor->sum('ventas'),
            'facturado'      => $porRevendedor->sum('facturado'),
            'comisiones'     => $porRevendedor->sum('comision_total'),
            'a_pagar'        => (float) $deudaViva->sum(),
        ];

        return view('revendedores.index', compact(
            'revendedores', 'porRevendedor', 'deudaViva', 'resumen', 'desde', 'hasta', 'estado', 'buscar'
        ));
    }

    /** Ficha del revendedor: sus ventas, sus comisiones y sus liquidaciones */
    public function show(Request $request, int $id)
    {
        $revendedor = Revendedor::findOrFail($id);

        $comisiones = RevendedorComision::with(['order.cliente', 'order.status', 'venta.cliente'])
            ->where('revendedor_id', $id)
            ->orderByDesc('created_at')
            ->get();

        $pagos = RevendedorPago::where('revendedor_id', $id)
            ->orderByDesc('fecha')
            ->get();

        $totales = [
            'ventas'     => $comisiones->where('estado', '!=', 'anulada')->count(),
            'facturado'  => $comisiones->where('estado', '!=', 'anulada')->sum('monto_venta'),
            'generado'   => $comisiones->where('estado', '!=', 'anulada')->sum('comision'),
            'pagado'     => $comisiones->where('estado', 'pagada')->sum('comision'),
            'a_pagar'    => $comisiones->whereIn('estado', ['pendiente', 'aprobada'])->sum('comision'),
            'aprobado'   => $comisiones->where('estado', 'aprobada')->sum('comision'),
        ];

        $qrDataUri = RevendedorPublicController::qrDataUri($revendedor->link, 400);

        return view('revendedores.show', compact('revendedor', 'comisiones', 'pagos', 'totales', 'qrDataUri'));
    }

    /** Cambiar comisión, estado o datos bancarios del revendedor */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'comision_porcentaje' => 'required|numeric|min:0|max:100',
            'estado' => 'required|in:pendiente,activo,suspendido',
            'cbu' => 'nullable|string|max:40',
            'alias_cbu' => 'nullable|string|max:60',
            'titular_cuenta' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:40',
            'notas' => 'nullable|string|max:2000',
        ]);

        $revendedor = Revendedor::findOrFail($id);
        $revendedor->update($request->only([
            'comision_porcentaje', 'estado', 'cbu', 'alias_cbu',
            'titular_cuenta', 'telefono', 'notas',
        ]));

        return back()->with('exito', 'Se actualizaron los datos de ' . $revendedor->nombre . '.');
    }

    /** Marca una comisión puntual como aprobada / anulada / pendiente */
    public function estadoComision(Request $request, int $id)
    {
        $request->validate(['estado' => 'required|in:pendiente,aprobada,anulada']);

        $comision = RevendedorComision::findOrFail($id);

        if ($comision->estado === 'pagada') {
            return back()->with('error', 'Esa comisión ya fue liquidada. Registrá un ajuste en su lugar.');
        }

        $comision->update(['estado' => $request->estado]);

        return back()->with('exito', 'Comisión actualizada.');
    }

    /**
     * Liquidación: paga todas las comisiones aprobadas (o también las pendientes)
     * y deja el comprobante registrado.
     */
    public function liquidar(Request $request, int $id)
    {
        $request->validate([
            'medio' => 'required|string|max:40',
            'referencia' => 'nullable|string|max:120',
            'observacion' => 'nullable|string|max:1000',
            'incluir_pendientes' => 'nullable',
        ]);

        $revendedor = Revendedor::findOrFail($id);

        $estados = $request->boolean('incluir_pendientes')
            ? ['aprobada', 'pendiente']
            : ['aprobada'];

        DB::beginTransaction();
        try {
            $comisiones = RevendedorComision::where('revendedor_id', $id)
                ->whereIn('estado', $estados)
                ->lockForUpdate()
                ->get();

            if ($comisiones->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'No hay comisiones para liquidar con ese criterio.');
            }

            $monto = round($comisiones->sum('comision'), 2);

            $pago = RevendedorPago::create([
                'revendedor_id' => $id,
                'monto'         => $monto,
                'fecha'         => now()->toDateString(),
                'medio'         => $request->medio,
                'referencia'    => $request->referencia,
                'observacion'   => $request->observacion,
                'usuario_id'    => Auth::id(),
            ]);

            RevendedorComision::whereIn('id', $comisiones->pluck('id'))
                ->update([
                    'estado'    => 'pagada',
                    'pago_id'   => $pago->id,
                    'pagada_at' => now(),
                ]);

            DB::commit();

            return back()->with('exito', 'Liquidaste $' . number_format($monto, 2, ',', '.') . ' a ' . $revendedor->nombre . ' (' . $comisiones->count() . ' ventas).');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'No se pudo liquidar: ' . $e->getMessage());
        }
    }

    /** QR del revendedor para imprimir o mandarle */
    public function qr(int $id)
    {
        $revendedor = Revendedor::findOrFail($id);

        return response(RevendedorPublicController::generarQrPng($revendedor->link), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="QR-Sommy-' . $revendedor->codigo . '.png"',
        ]);
    }

    /** CSV con el detalle de comisiones del período */
    public function exportComisiones(Request $request)
    {
        $desde = $request->input('desde', now()->startOfYear()->toDateString());
        $hasta = $request->input('hasta', now()->toDateString());

        $comisiones = RevendedorComision::with(['revendedor', 'order.cliente', 'venta.cliente'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$desde, $hasta])
            ->when($request->filled('revendedor_id'), fn ($q) => $q->where('revendedor_id', $request->revendedor_id))
            ->orderBy('revendedor_id')
            ->orderBy('created_at')
            ->get();

        $nombre = 'comisiones_revendedores_' . $desde . '_a_' . $hasta . '.csv';

        return response()->streamDownload(function () use ($comisiones) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel respete los acentos
            fputcsv($out, ['Fecha', 'Revendedor', 'Codigo', 'Pedido', 'Cliente', 'Venta', '% Comision', 'Comision', 'Estado', 'Pagada el'], ';');

            foreach ($comisiones as $c) {
                fputcsv($out, [
                    $c->created_at->format('d/m/Y'),
                    optional($c->revendedor)->nombre,
                    optional($c->revendedor)->codigo,
                    $c->order_id ? ('Pedido #' . $c->order_id) : ('Venta #' . $c->venta_id),
                    optional(optional($c->order_id ? $c->order : $c->venta)->cliente)->nombre,
                    number_format($c->monto_venta, 2, ',', ''),
                    number_format($c->porcentaje, 2, ',', ''),
                    number_format($c->comision, 2, ',', ''),
                    ucfirst($c->estado),
                    $c->pagada_at ? $c->pagada_at->format('d/m/Y') : '',
                ], ';');
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
