<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Envio;
use App\Models\Gasto;
use App\Models\GastoCategoria;
use App\Models\Transportista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class EnvioController extends Controller
{
    private const BADGES = [
        'pendiente'  => 'badge-secondary',
        'despachado' => 'badge-info',
        'en_transito' => 'badge-primary',
        'entregado'  => 'badge-success',
        'fallido'    => 'badge-danger',
    ];

    private const LABELS = [
        'pendiente'   => 'Pendiente',
        'despachado'  => 'Despachado',
        'en_transito' => 'En tránsito',
        'entregado'   => 'Entregado',
        'fallido'     => 'Fallido',
    ];

    public function index()
    {
        Gate::authorize('haveaccess', 'finanzas.envios.index');

        $transportistas = Transportista::where('activo', true)->orderBy('nombre')->get();

        return view('finanzas.envios.index', compact('transportistas'));
    }

    public function list(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.envios.index');

        $envios = Envio::with(['transportista', 'orden.cliente', 'compra.proveedor', 'venta.cliente']);

        if ($request->filled('estado')) {
            $envios->where('estado', $request->estado);
        }
        if ($request->filled('transportista_id')) {
            $envios->where('transportista_id', $request->transportista_id);
        }

        return DataTables::of($envios)
            ->addColumn('referencia', function ($e) {
                if ($e->tipo === 'venta' && $e->order_ecommerce_id) {
                    $cliente = $e->orden->cliente->nombre ?? '';

                    return 'Pedido #' . $e->order_ecommerce_id . ($cliente ? ' — ' . $cliente : '');
                }
                if ($e->tipo === 'compra' && $e->compra_id) {
                    $proveedor = $e->compra->proveedor->nombre ?? '';

                    return 'Compra #' . $e->compra_id . ($proveedor ? ' — ' . $proveedor : '');
                }
                if ($e->tipo === 'venta_manual' && $e->venta_id) {
                    $cliente = $e->venta->cliente->nombre ?? '';

                    return 'Venta #' . $e->venta_id . ($cliente ? ' — ' . $cliente : '');
                }

                return '-';
            })
            ->editColumn('tipo', fn ($e) => ['venta' => 'Venta', 'compra' => 'Compra', 'venta_manual' => 'Venta manual'][$e->tipo] ?? $e->tipo)
            ->addColumn('transportista', fn ($e) => $e->transportista->nombre ?? '-')
            ->editColumn('costo', fn ($e) => '$' . number_format($e->costo, 2, ',', '.'))
            ->editColumn('pagado_por', fn ($e) => $e->pagado_por === 'empresa' ? 'Empresa' : 'Cliente')
            ->editColumn('estado', function ($e) {
                $badge = self::BADGES[$e->estado] ?? 'badge-secondary';
                $label = self::LABELS[$e->estado] ?? $e->estado;

                return '<span class="badge ' . $badge . '">' . e($label) . '</span>';
            })
            ->addColumn('fechas', function ($e) {
                $out = [];
                if ($e->fecha_despacho) {
                    $out[] = 'Desp: ' . $e->fecha_despacho->format('d/m/Y');
                }
                if ($e->fecha_entrega_estimada) {
                    $out[] = 'Est: ' . $e->fecha_entrega_estimada->format('d/m/Y');
                }
                if ($e->fecha_entrega_real) {
                    $out[] = 'Entr: ' . $e->fecha_entrega_real->format('d/m/Y');
                }

                return implode('<br>', $out);
            })
            ->addColumn('action', function ($e) {
                $btns = '';
                $siguiente = $this->siguienteEstado($e->estado);

                if ($siguiente) {
                    $btns .= '<button class="btn btn-sm btn-success" title="Marcar como ' . self::LABELS[$siguiente] . '" onclick="avanzarEstadoEnvio(' . $e->id . ', \'' . $siguiente . '\', \'' . self::LABELS[$siguiente] . '\')"><i class="fa fa-arrow-right"></i> ' . self::LABELS[$siguiente] . '</button> ';
                }
                if (!in_array($e->estado, ['entregado', 'fallido'])) {
                    $btns .= '<button class="btn btn-sm btn-outline-danger" title="Marcar como fallido" onclick="avanzarEstadoEnvio(' . $e->id . ', \'fallido\', \'Fallido\')"><i class="fa fa-times"></i></button> ';
                }

                $datos = e(json_encode([
                    'id'                     => $e->id,
                    'transportista_id'       => $e->transportista_id,
                    'costo'                  => (float) $e->costo,
                    'pagado_por'             => $e->pagado_por,
                    'tracking'               => $e->tracking,
                    'direccion_entrega'      => $e->direccion_entrega,
                    'fecha_entrega_estimada' => $e->fecha_entrega_estimada?->format('Y-m-d'),
                    'notas'                  => $e->notas,
                ]));

                $btns .= '<button class="btn btn-sm btn-primary" title="Editar" data-envio="' . $datos . '" onclick="editarEnvio(this)"><i class="fa fa-edit"></i></button> ';
                $btns .= '<button class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminarEnvio(' . $e->id . ')"><i class="fa fa-trash"></i></button>';

                return $btns;
            })
            ->rawColumns(['estado', 'fechas', 'action'])
            ->make(true);
    }

    /**
     * Alta de un envío desde el detalle de un pedido, de una compra o desde el index.
     * Si el flete lo paga la empresa, genera automáticamente el gasto pendiente
     * en la categoría "Fletes".
     */
    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.envios.store');

        $request->validate([
            'tipo'                   => 'required|in:venta,compra,venta_manual',
            'order_ecommerce_id'     => 'required_if:tipo,venta|nullable|exists:order_ecommerce,order_id',
            'compra_id'              => 'required_if:tipo,compra|nullable|exists:compras,idcompra',
            'venta_id'               => 'required_if:tipo,venta_manual|nullable|exists:ventas,idventa',
            'transportista_id'       => 'required|exists:transportistas,id',
            'costo'                  => 'nullable|numeric|min:0',
            'pagado_por'             => 'required|in:cliente,empresa',
            'cuenta'                 => 'nullable|string|max:30',
            'direccion_entrega'      => 'nullable|string|max:255',
            'fecha_entrega_estimada' => 'nullable|date',
            'tracking'               => 'nullable|string|max:120',
            'notas'                  => 'nullable|string',
        ], [
            'order_ecommerce_id.required_if' => 'Indicá el número de pedido.',
            'compra_id.required_if'          => 'Indicá el número de compra.',
            'venta_id.required_if'           => 'Indicá el número de venta.',
            'transportista_id.required'      => 'Seleccioná el transportista.',
        ]);

        DB::beginTransaction();
        try {
            $envio = Envio::create([
                'tipo'                   => $request->tipo,
                'order_ecommerce_id'     => $request->tipo === 'venta' ? $request->order_ecommerce_id : null,
                'compra_id'              => $request->tipo === 'compra' ? $request->compra_id : null,
                'venta_id'               => $request->tipo === 'venta_manual' ? $request->venta_id : null,
                'transportista_id'       => $request->transportista_id,
                'costo'                  => $request->costo ?: 0,
                'pagado_por'             => $request->pagado_por,
                'estado'                 => 'pendiente',
                'direccion_entrega'      => $request->direccion_entrega,
                'fecha_entrega_estimada' => $request->fecha_entrega_estimada,
                'tracking'               => $request->tracking,
                'notas'                  => $request->notas,
            ]);

            // Si el flete lo paga la empresa, se genera el gasto pendiente en "Fletes"
            if ($request->pagado_por === 'empresa' && (float) ($request->costo ?: 0) > 0) {
                $categoria = GastoCategoria::firstOrCreate(
                    ['nombre' => 'Fletes'],
                    ['activo' => true]
                );

                $transportista = Transportista::find($request->transportista_id);

                $gasto = Gasto::create([
                    'fecha'              => now()->toDateString(),
                    'gasto_categoria_id' => $categoria->id,
                    'descripcion'        => 'Flete envío #' . $envio->id . ($transportista ? ' — ' . $transportista->nombre : ''),
                    'monto'              => $request->costo,
                    'user_id'            => auth()->id(),
                    'estado'             => 'pendiente',
                ]);

                $envio->update(['gasto_id' => $gasto->id]);
            }

            DB::commit();

            // Si eligieron de qué caja/banco sale la plata, el gasto de flete se paga en el acto
            $avisoPago = null;
            if ($envio->gasto_id && $request->filled('cuenta')) {
                $pago = app(GastoController::class)
                    ->registrarPago(new Request(['cuenta' => $request->cuenta]), $envio->gasto_id)
                    ->getData(true);
                $avisoPago = $pago['estado'] === 1
                    ? 'Flete pagado desde la cuenta seleccionada.'
                    : 'El envío se registró, pero el pago del flete falló: ' . ($pago['mensaje'] ?? '') . ' Quedó pendiente en Gastos.';
            }

            return response()->json([
                'estado' => 1,
                'mensaje' => trim('Envío registrado correctamente. ' . ($avisoPago ?? '')),
                'id' => $envio->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['estado' => 0, 'mensaje' => 'Error al registrar el envío: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'finanzas.envios.update');

        $envio = Envio::findOrFail($id);

        $data = $request->validate([
            'transportista_id'       => 'required|exists:transportistas,id',
            'costo'                  => 'nullable|numeric|min:0',
            'pagado_por'             => 'required|in:cliente,empresa',
            'direccion_entrega'      => 'nullable|string|max:255',
            'fecha_entrega_estimada' => 'nullable|date',
            'tracking'               => 'nullable|string|max:120',
            'notas'                  => 'nullable|string',
        ]);

        $data['costo'] = $data['costo'] ?? 0;

        DB::beginTransaction();
        try {
            $envio->update($data);

            // Mantener sincronizado el gasto de flete si existe y sigue pendiente
            if ($envio->gasto_id && $envio->gasto && $envio->gasto->estado === 'pendiente') {
                $envio->gasto->update(['monto' => $envio->costo]);
            }

            DB::commit();

            return response()->json(['estado' => 1, 'mensaje' => 'Envío actualizado correctamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['estado' => 0, 'mensaje' => 'Error al actualizar el envío: ' . $e->getMessage()]);
        }
    }

    /**
     * Avanza el estado del envío. despachado => fecha_despacho, entregado => fecha_entrega_real.
     */
    public function setEstado(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'finanzas.envios.estado');

        $request->validate([
            'estado' => 'required|in:pendiente,despachado,en_transito,entregado,fallido',
        ]);

        $envio = Envio::findOrFail($id);

        $envio->estado = $request->estado;

        if ($request->estado === 'despachado' && !$envio->fecha_despacho) {
            $envio->fecha_despacho = now();
        }
        if ($request->estado === 'entregado' && !$envio->fecha_entrega_real) {
            $envio->fecha_entrega_real = now();
        }

        $envio->save();

        return response()->json([
            'estado'  => 1,
            'mensaje' => 'El envío pasó a "' . (self::LABELS[$envio->estado] ?? $envio->estado) . '".',
        ]);
    }

    public function destroy($id)
    {
        Gate::authorize('haveaccess', 'finanzas.envios.destroy');

        $envio = Envio::with('gasto')->findOrFail($id);

        if ($envio->gasto && $envio->gasto->estado === 'pagado') {
            return response()->json(['estado' => 0, 'mensaje' => 'El flete de este envío ya fue pagado: no se puede eliminar.']);
        }

        DB::beginTransaction();
        try {
            $gasto = $envio->gasto;
            $envio->delete();

            // Si tenía un gasto de flete pendiente, se elimina junto con el envío
            if ($gasto && $gasto->estado === 'pendiente' && !$gasto->movimiento_id) {
                $gasto->delete();
            }

            DB::commit();

            return response()->json(['estado' => 1, 'mensaje' => 'Envío eliminado correctamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['estado' => 0, 'mensaje' => 'Error al eliminar el envío: ' . $e->getMessage()]);
        }
    }

    private function siguienteEstado(string $actual): ?string
    {
        return match ($actual) {
            'pendiente'   => 'despachado',
            'despachado'  => 'en_transito',
            'en_transito' => 'entregado',
            default       => null,
        };
    }
}
