<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\CajaApertura;
use App\Models\Gasto;
use App\Models\GastoCategoria;
use App\Models\Movimiento;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Services\ChequeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class GastoController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.index');

        $categorias  = GastoCategoria::where('activo', true)->orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();
        $sucursales  = Sucursal::where('activo', 1)->get();

        return view('finanzas.gastos.index', compact('categorias', 'proveedores', 'sucursales'));
    }

    /**
     * Listado server-side para DataTables, con filtros por fecha, categoría y estado.
     */
    public function list(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.index');

        $gastos = Gasto::with(['categoria', 'proveedor', 'cuenta', 'sucursal']);

        if ($request->filled('fecha_desde')) {
            $gastos->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $gastos->whereDate('fecha', '<=', $request->fecha_hasta);
        }
        if ($request->filled('categoria_id')) {
            $gastos->where('gasto_categoria_id', $request->categoria_id);
        }
        if ($request->filled('estado')) {
            $gastos->where('estado', $request->estado);
        }

        return DataTables::of($gastos)
            ->editColumn('fecha', fn ($g) => $g->fecha->format('d/m/Y'))
            ->addColumn('categoria', fn ($g) => $g->categoria->nombre ?? '-')
            ->addColumn('proveedor', fn ($g) => $g->proveedor->nombre ?? '-')
            ->editColumn('monto', fn ($g) => '$' . number_format($g->monto, 2, ',', '.'))
            ->addColumn('recurrente', function ($g) {
                if (!$g->es_recurrente) {
                    return '';
                }
                $prox = $g->proximo_vencimiento ? ' · próx. ' . $g->proximo_vencimiento->format('d/m/Y') : '';

                return '<span class="badge badge-info">' . e(ucfirst($g->frecuencia)) . $prox . '</span>';
            })
            ->editColumn('estado', function ($g) {
                return $g->estado === 'pagado'
                    ? '<span class="badge badge-success">Pagado</span>'
                    : '<span class="badge badge-warning">Pendiente</span>';
            })
            ->addColumn('comprobante', function ($g) {
                if (!$g->comprobante_path) {
                    return '';
                }

                return '<a href="' . e(Storage::url($g->comprobante_path)) . '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa fa-paperclip"></i></a>';
            })
            ->addColumn('action', function ($g) {
                $btns = '';
                if ($g->estado === 'pendiente') {
                    $btns .= '<button class="btn btn-sm btn-success" title="Registrar pago" onclick="abrirModalPagoGasto(' . $g->id . ')"><i class="fa fa-dollar-sign"></i></button> ';
                    $btns .= '<button class="btn btn-sm btn-primary" title="Editar" onclick="editarGasto(' . $g->id . ')"><i class="fa fa-edit"></i></button> ';
                }
                if (!$g->movimiento_id) {
                    $btns .= '<button class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminarGasto(' . $g->id . ')"><i class="fa fa-trash"></i></button>';
                }

                return $btns;
            })
            ->rawColumns(['recurrente', 'estado', 'comprobante', 'action'])
            ->make(true);
    }

    /**
     * Datos de un gasto para cargar el modal de edición.
     */
    public function show($id)
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.index');

        $gasto = Gasto::with(['categoria', 'proveedor'])->findOrFail($id);

        return response()->json([
            'estado' => 1,
            'gasto'  => [
                'id'                  => $gasto->id,
                'fecha'               => $gasto->fecha->format('Y-m-d'),
                'gasto_categoria_id'  => $gasto->gasto_categoria_id,
                'proveedor_id'        => $gasto->proveedor_id,
                'descripcion'         => $gasto->descripcion,
                'monto'               => (float) $gasto->monto,
                'sucursal_id'         => $gasto->sucursal_id,
                'es_recurrente'       => (bool) $gasto->es_recurrente,
                'frecuencia'          => $gasto->frecuencia,
                'proximo_vencimiento' => $gasto->proximo_vencimiento?->format('Y-m-d'),
                'estado'              => $gasto->estado,
                'monto_formateado'    => number_format($gasto->monto, 2, ',', '.'),
                'descripcion_pago'    => ($gasto->proveedor->nombre ?? null) ? $gasto->proveedor->nombre . ' — ' . $gasto->descripcion : $gasto->descripcion,
            ],
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.manage');

        $data = $this->validarGasto($request);

        $data['user_id'] = auth()->id();
        $data['estado']  = 'pendiente';

        if ($request->hasFile('comprobante')) {
            $data['comprobante_path'] = $request->file('comprobante')->store('gastos', 'public');
        }

        $gasto = Gasto::create($data);

        // Si eligieron con qué caja/banco se pagó, el gasto se paga en el acto:
        // genera el movimiento de egreso y descuenta en el cierre diario de esa caja.
        if ($request->filled('cuenta')) {
            $pago = $this->registrarPago(new Request(['cuenta' => $request->cuenta]), $gasto->id)->getData(true);

            if (($pago['estado'] ?? 0) === 1) {
                return response()->json(['estado' => 1, 'mensaje' => 'Gasto registrado y pagado: ya descuenta en el cierre de la caja.', 'id' => $gasto->id]);
            }

            return response()->json([
                'estado' => 1,
                'mensaje' => 'Gasto registrado, pero el pago falló: ' . ($pago['mensaje'] ?? '') . ' Quedó pendiente.',
                'id' => $gasto->id,
            ]);
        }

        return response()->json(['estado' => 1, 'mensaje' => 'Gasto registrado correctamente (pendiente de pago).', 'id' => $gasto->id]);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.manage');

        $gasto = Gasto::findOrFail($id);

        if ($gasto->estado === 'pagado') {
            return response()->json(['estado' => 0, 'mensaje' => 'No se puede editar un gasto ya pagado.']);
        }

        $data = $this->validarGasto($request);

        if ($request->hasFile('comprobante')) {
            if ($gasto->comprobante_path) {
                Storage::disk('public')->delete($gasto->comprobante_path);
            }
            $data['comprobante_path'] = $request->file('comprobante')->store('gastos', 'public');
        }

        $gasto->update($data);

        return response()->json(['estado' => 1, 'mensaje' => 'Gasto actualizado correctamente.']);
    }

    public function destroy($id)
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.manage');

        $gasto = Gasto::findOrFail($id);

        if ($gasto->movimiento_id) {
            return response()->json(['estado' => 0, 'mensaje' => 'El gasto ya tiene un pago registrado en tesorería: no se puede eliminar.']);
        }

        if ($gasto->comprobante_path) {
            Storage::disk('public')->delete($gasto->comprobante_path);
        }

        $gasto->delete();

        return response()->json(['estado' => 1, 'mensaje' => 'Gasto eliminado correctamente.']);
    }

    /**
     * Registra el pago del gasto: crea el egreso en tesorería y marca el gasto como pagado.
     * La cuenta llega como "caja-{aperturaId}" o "banco-{cuentaId}".
     */
    public function registrarPago(Request $request, $id, ChequeService $chequeService)
    {
        Gate::authorize('haveaccess', 'finanzas.gastos.manage');

        $request->validate([
            'cuenta' => 'required|string',
        ], [
            'cuenta.required' => 'Seleccioná la cuenta desde la que se paga.',
        ]);

        $gasto = Gasto::with('proveedor')->findOrFail($id);

        if ($gasto->estado === 'pagado') {
            return response()->json(['estado' => 0, 'mensaje' => 'El gasto ya está pagado.']);
        }

        // Endoso: se paga entregando un cheque de tercero que ya está en cartera.
        $chequeEndosado = str_starts_with($request->cuenta, 'cheque-')
            ? $chequeService->resolverEndoso($request->cuenta)
            : null;
        if (str_starts_with($request->cuenta, 'cheque-') && $request->cuenta !== 'cheque-nuevo' && !$chequeEndosado) {
            return response()->json(['estado' => 0, 'mensaje' => 'Ese cheque ya no está disponible para entregar.']);
        }
        $esChequeNuevo = $request->cuenta === 'cheque-nuevo';
        if ($esChequeNuevo && !$request->input('cheque_numero')) {
            return response()->json(['estado' => 0, 'mensaje' => 'Indicá el número del cheque.']);
        }

        if ($chequeEndosado && abs((float) $chequeEndosado->monto - (float) $gasto->monto) > 0.01) {
            return response()->json(['estado' => 0, 'mensaje' => 'El cheque es de $' . number_format($chequeEndosado->monto, 2, ',', '.') . ' y el gasto es de $' . number_format($gasto->monto, 2, ',', '.') . ': no coinciden (el gasto se paga completo).']);
        }

        $monto = $chequeEndosado ? (float) $chequeEndosado->monto : (float) $gasto->monto;

        DB::beginTransaction();
        try {
            $cuentaId   = null;
            $aperturaId = null;
            $efectivo   = 0;
            $bancos     = 0;
            $montoCheque = 0;

            if ($chequeEndosado || $esChequeNuevo) {
                $montoCheque = $monto;
            } elseif (str_starts_with($request->cuenta, 'caja-')) {
                $aperturaId = (int) str_replace('caja-', '', $request->cuenta);
                $apertura   = CajaApertura::findOrFail($aperturaId);

                if (!$apertura->estaAbierta()) {
                    DB::rollBack();

                    return response()->json(['estado' => 0, 'mensaje' => 'La caja seleccionada no está abierta.']);
                }

                $cuentaId = $apertura->cuenta_id;
                $efectivo = $monto;
            } elseif (str_starts_with($request->cuenta, 'banco-')) {
                $cuentaId = (int) str_replace('banco-', '', $request->cuenta);
                $bancos   = $monto;
            } else {
                DB::rollBack();

                return response()->json(['estado' => 0, 'mensaje' => 'Formato de cuenta inválido.']);
            }

            $medio = ($chequeEndosado || $esChequeNuevo) ? 'cheque' : null;

            $movimiento = Movimiento::create([
                'cuenta_id'         => $cuentaId,
                'caja_apertura_id'  => $aperturaId,
                'fecha'             => now(),
                'tipo'              => 'egreso',
                'medio'             => $medio,
                'cliente_proveedor' => $gasto->proveedor->nombre ?? $gasto->descripcion,
                'comprobante'       => 'Gasto #' . $gasto->id,
                'observaciones'     => 'Pago de gasto: ' . $gasto->descripcion
                    . ($chequeEndosado ? ' — entregado cheque Nº ' . $chequeEndosado->numero : ''),
                'efectivo'          => $efectivo,
                'bancos'            => $bancos,
                'tarjetas'          => 0,
                'cheques'           => $montoCheque,
                'total'             => $monto,
                'referencia_type'   => Gasto::class,
                'referencia_id'     => $gasto->id,
            ]);

            $gasto->update([
                'estado'        => 'pagado',
                'cuenta_id'     => $cuentaId,
                'movimiento_id' => $movimiento->id,
            ]);

            if ($chequeEndosado) {
                $chequeService->entregar($chequeEndosado, $movimiento);
            } elseif ($esChequeNuevo) {
                $chequeService->registrarPropio([
                    'numero'             => $request->input('cheque_numero'),
                    'banco_emisor'       => $request->input('cheque_banco'),
                    'contraparte_nombre' => $request->input('cheque_titular') ?: ($gasto->proveedor->nombre ?? $gasto->descripcion),
                    'monto'              => $monto,
                    'fecha_cobro'        => $request->input('cheque_fecha_cobro') ?: now(),
                ], $gasto, $movimiento);
            }

            DB::commit();

            return response()->json(['estado' => 1, 'mensaje' => 'Pago registrado correctamente.']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['estado' => 0, 'mensaje' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }

    private function validarGasto(Request $request): array
    {
        $data = $request->validate([
            'fecha'               => 'required|date',
            'gasto_categoria_id'  => 'required|exists:gasto_categorias,id',
            'proveedor_id'        => 'nullable|exists:proveedores,idproveedor',
            'descripcion'         => 'required|string|max:255',
            'monto'               => 'required|numeric|min:0.01',
            'sucursal_id'         => 'nullable|exists:sucursales,id',
            'es_recurrente'       => 'nullable|boolean',
            'frecuencia'          => 'nullable|required_if:es_recurrente,1|in:semanal,mensual,anual',
            'proximo_vencimiento' => 'nullable|required_if:es_recurrente,1|date',
            'comprobante'         => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'descripcion.required'            => 'Ingresá una descripción del gasto.',
            'monto.required'                  => 'Ingresá el monto.',
            'monto.min'                       => 'El monto debe ser mayor a cero.',
            'gasto_categoria_id.required'     => 'Seleccioná una categoría.',
            'frecuencia.required_if'          => 'Si el gasto es recurrente, indicá la frecuencia.',
            'proximo_vencimiento.required_if' => 'Si el gasto es recurrente, indicá el próximo vencimiento.',
            'comprobante.mimes'               => 'El comprobante debe ser JPG, PNG o PDF.',
            'comprobante.max'                 => 'El comprobante no puede superar los 5 MB.',
        ]);

        unset($data['comprobante']);

        $data['es_recurrente'] = $request->boolean('es_recurrente');
        if (!$data['es_recurrente']) {
            $data['frecuencia']          = null;
            $data['proximo_vencimiento'] = null;
        }

        return $data;
    }
}
