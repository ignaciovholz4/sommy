<?php

namespace App\Http\Controllers\Unidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Unidad;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class UnidadController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess','almacen_unidad.index');
        return view('almacen.unidad.index');
    }

    public function store(Request $request)
    {
        $rules = [
            'nombre' => 'required',
            'nombre_corto' => 'required|max:10',
            'decimal' => 'boolean'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status'=> 0, 'error' => $validator->errors()->all()]);
        }

        $unidad = new Unidad;
        $unidad->nombre = $request->nombre;
        $unidad->nombre_corto = $request->nombre_corto;
        $unidad->decimal = $request->decimal ?? 0;
        $unidad->status = 1;
        $unidad->save();

        return response()->json(['status'=> 1, 'message' => 'Unidad guardada']);
    }

    public function show()
    {
        $data = DB::table('unidades')->where('status','=','1')->get();
        return DataTables::of($data)
            ->addColumn('action', function($data){
                $id = $data->idunidad;
                $button ='<i class="fas fa-edit text-primary mr-3" onclick="edit_unidad('.$id.');"></i>';
                $button .='<i class="fas fa-trash-alt text-danger" onclick="delete_unidad('.$id.');"></i>';
                return $button;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function edit($id)
    {
        $unidad = Unidad::find($id);
        return Response::json($unidad);
    }

    public function update(Request $request)
    {
        $rules = [
            'upid' => 'required',
            'upnombre' => 'required',
            'upnombre_corto' => 'required|max:10',
            'updecimal' => 'boolean'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $unidad = Unidad::find($request->upid);
        $unidad->nombre = $request->upnombre;
        $unidad->nombre_corto = $request->upnombre_corto;
        $unidad->decimal = $request->updecimal ?? 0;
        $unidad->update();

        return response()->json(['estado'=> 1, 'mensaje' => 'Unidad actualizada']);
    }

    public function destroy(Request $request)
    {
        $unidad = Unidad::find($request->id);
        $unidad->status = 0;
        $unidad->update();
        return response()->json(['estado'=> 1, 'mensaje' => 'Unidad eliminada']);
    }
}