<?php

namespace App\Http\Controllers\Articulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Models\producto\Color;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('haveaccess','almacen_color.index');
        return view('almacen.colorp.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if($request->ajax())
       {
            $rules = [
                'id' => 'required',
                'nombre' => 'required',
                'hexadecimal' => 'required'
            ];

            $messages = [
                'id.required'=>'El identificador es requerido',
                'nombre.required'=>'El nombre es requerido',
                'hexadecimal.required'=>'El exadecimal es requerida'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                
                return response()->json(['error'=>$validator->errors()->all()]);
            }
            
            try {
                $message = "";
                if($request->id == 0){
                    $talla = new Color;
                    $talla->name = $request->nombre;
                    $talla->hexadecimal = $request->hexadecimal;
                    $talla->save();
                    $message = "Se guardo el color";
                }
                if($request->id > 0){
                    $talla = Color::find($request->id);
                    $talla->name = $request->nombre;
                    $talla->hexadecimal = $request->hexadecimal;
                    $talla->update();
                    $message = "Se actualizo el color";
                }
                return response()->json([
                    'estado'=> '1',  
                    'mensaje' => $message
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    'estado'=> '0',
                    'mensaje' => 'Ocurrio un error, intentalo de nuevo'
                ]);
            }
            
        }
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $data = DB::table('color')->where('status','=',1)->get();

        return DataTables::of($data)
        ->addColumn('action', function($data){
            $id = $data->id;
            //$talla_producto = DB::table('productos')->where('color_id',$id)->first();
            //if ($talla_producto == null) {
                $button ='<i class="fas fa-edit text-primary mr-3" id="" onclick="edit_color('.$id.');" title="Actualizar el color"></i>';
                $button .='<i class="fas fa-trash-alt text-danger" onclick="delete_color('.$id.');" data-toggle="tooltip" title="Eliminar el color"></i>';
            /*}else{
                $button ='<i class="fas fa-edit text-primary mr-3" id="" onclick="edit_color('.$id.');" title="Actualizar el color"></i>';
                $button .='<i class="fas fa-trash-alt text-warning" data-toggle="tooltip" title="Esta en uso el color"></i>';
            }*/
            return $button;

        })
        ->rawColumns(['action'])
        ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $where = array('id' => $id);
        $talla = Color::where($where)->first();
        return Response::json($talla);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $id = $request->id;
            $color = Color::find($id);
            $color->estatus=0;
            $color->update();
            
            return response()->json([
                'estado'=> 1,  
                'mensaje' => 'Se elimino el color con exito'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'estado'=> 0,  
                'mensaje' => 'Error: No se pudo eliminar intentalo de nuevo'
            ]);
        }
    }
}
