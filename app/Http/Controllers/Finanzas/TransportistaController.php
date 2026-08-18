<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Transportista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class TransportistaController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'finanzas.transportistas.manage');

        return view('finanzas.transportistas.index');
    }

    public function list(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.transportistas.manage');

        $transportistas = Transportista::withCount('envios');

        return DataTables::of($transportistas)
            ->editColumn('activo', function ($t) {
                return $t->activo
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-secondary">Inactivo</span>';
            })
            ->addColumn('action', function ($t) {
                $datos = e(json_encode([
                    'id'       => $t->id,
                    'nombre'   => $t->nombre,
                    'cuit'     => $t->cuit,
                    'telefono' => $t->telefono,
                    'email'    => $t->email,
                    'notas'    => $t->notas,
                    'activo'   => (bool) $t->activo,
                ]));

                return '<button class="btn btn-sm btn-primary" title="Editar" data-transportista="' . $datos . '" onclick="editarTransportista(this)"><i class="fa fa-edit"></i></button> '
                    . '<button class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminarTransportista(' . $t->id . ')"><i class="fa fa-trash"></i></button>';
            })
            ->rawColumns(['activo', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        Gate::authorize('haveaccess', 'finanzas.transportistas.manage');

        $data = $this->validarTransportista($request);
        $data['activo'] = true;

        Transportista::create($data);

        return response()->json(['estado' => 1, 'mensaje' => 'Transportista creado correctamente.']);
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'finanzas.transportistas.manage');

        $transportista = Transportista::findOrFail($id);

        $data = $this->validarTransportista($request);
        $data['activo'] = $request->boolean('activo', true);

        $transportista->update($data);

        return response()->json(['estado' => 1, 'mensaje' => 'Transportista actualizado correctamente.']);
    }

    public function destroy($id)
    {
        Gate::authorize('haveaccess', 'finanzas.transportistas.manage');

        $transportista = Transportista::withCount('envios')->findOrFail($id);

        if ($transportista->envios_count > 0) {
            // Tiene envíos asociados: se desactiva para no romper el historial
            $transportista->update(['activo' => false]);

            return response()->json(['estado' => 1, 'mensaje' => 'El transportista tiene envíos asociados: se desactivó en lugar de borrarse.']);
        }

        $transportista->delete();

        return response()->json(['estado' => 1, 'mensaje' => 'Transportista eliminado correctamente.']);
    }

    private function validarTransportista(Request $request): array
    {
        return $request->validate([
            'nombre'   => 'required|string|max:150',
            'cuit'     => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'email'    => 'nullable|email|max:150',
            'notas'    => 'nullable|string',
        ], [
            'nombre.required' => 'Ingresá el nombre del transportista.',
            'email.email'     => 'El email no tiene un formato válido.',
        ]);
    }
}
