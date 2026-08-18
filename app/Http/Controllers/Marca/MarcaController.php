<?php

namespace App\Http\Controllers\Marca;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Marca;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class MarcaController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess','almacen_marca.index');
        return view('almacen.marca.index');
    }

    public function store(Request $request)
    {
        $rules = [
            'nombre' => 'required',
            'descripcion' => 'nullable'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status'=> 0, 'error' => $validator->errors()->all()]);
        }

        $marca = new Marca;
        $marca->nombre = $request->nombre;
        $marca->descripcion = $request->descripcion;
        $marca->status = 1;
        $marca->save();

        return response()->json(['status'=> 1, 'message' => 'Marca guardada']);
    }

    public function show()
    {
        $data = DB::table('marcas')->where('status','=','1')->get();
        return DataTables::of($data)
            ->addColumn('action', function($data){
                $id = $data->idmarca;
                $button ='<i class="fas fa-edit text-primary mr-3" onclick="edit_marca('.$id.');"></i>';
                $button .='<i class="fas fa-trash-alt text-danger" onclick="delete_marca('.$id.');"></i>';
                return $button;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function edit($id)
    {
        $marca = Marca::find($id);
        return Response::json($marca);
    }

    public function update(Request $request)
    {
        $rules = [
            'upid' => 'required',
            'upnombre' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()->all()]);
        }

        $marca = Marca::find($request->upid);
        $marca->nombre = $request->upnombre;
        $marca->descripcion = $request->updescripcion;
        $marca->update();

        return response()->json(['estado'=> 1, 'mensaje' => 'Marca actualizada']);
    }

    public function destroy(Request $request)
    {
        $marca = Marca::find($request->id);
        $marca->status = 0;
        $marca->update();
        return response()->json(['estado'=> 1, 'mensaje' => 'Marca eliminada']);
    }
}
