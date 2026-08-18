<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ZonaEnvio;
use Yajra\Datatables\Datatables;

class ZonaEnvioController extends Controller
{
    public function index()
    {
        return view('admin.zonas_envio.index');
    }

    public function show()
    {
        $zonas = ZonaEnvio::orderBy('orden')->get();

        return DataTables::of($zonas)
            ->addColumn('costo_formateado', fn($z) => $z->costo > 0 ? format_money_global($z->costo) : 'Gratis / a coordinar')
            ->addColumn('estado', fn($z) => $z->activo
                ? '<span class="badge bg-success">Activa</span>'
                : '<span class="badge bg-secondary">Inactiva</span>')
            ->addColumn('action', function ($z) {
                return '
                    <div style="display:flex; gap:8px; justify-content:center;">
                        <button class="btn btn-sm btn-primary btn-edit-zona" data-id="'.$z->id.'"
                            data-nombre="'.e($z->nombre).'" data-costo="'.$z->costo.'"
                            data-orden="'.$z->orden.'" data-activo="'.($z->activo ? 1 : 0).'">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete-zona" data-id="'.$z->id.'">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['estado', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $rules = [
            'nombre' => 'required|string|max:100',
            'costo'  => 'required|numeric|min:0',
            'orden'  => 'nullable|integer|min:0',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'nombre.required' => 'El nombre de la zona es requerido',
            'costo.required'  => 'El costo es requerido (0 = gratis/a coordinar)',
            'costo.numeric'   => 'El costo debe ser un número',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->all()]);
        }

        $datos = [
            'nombre' => $request->nombre,
            'costo'  => $request->costo,
            'orden'  => $request->orden ?? 0,
            'activo' => $request->has('activo') ? (bool) $request->activo : true,
        ];

        if ((int) $request->zonaId > 0) {
            $zona = ZonaEnvio::findOrFail((int) $request->zonaId);
            $zona->update($datos);
            $mensaje = 'Zona de envío actualizada';
        } else {
            ZonaEnvio::create($datos);
            $mensaje = 'Zona de envío creada';
        }

        return response()->json(['status' => 1, 'message' => $mensaje]);
    }

    public function destroy(Request $request)
    {
        $zona = ZonaEnvio::find($request->id);

        if (!$zona) {
            return response()->json(['status' => 0, 'message' => ['No se encontró la zona']]);
        }

        // Baja lógica: si tiene pedidos asociados no conviene borrarla físicamente
        $zona->update(['activo' => false]);

        return response()->json(['status' => 1, 'message' => 'Zona desactivada']);
    }
}
