<?php

namespace App\Http\Controllers\Variaciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\variacion\Variacion;
use App\Models\variacion\Variantes_para_variacion;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;

class VariacionesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('almacen.variaciones.variacion.index');
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
            $rules = [
                'id' => 'required',
                'name' => 'required',
                'option_type' => 'required',
            ];

            $messages = [
                'id.required'=>'El identificador es requerido',
                'name.required'=>'El nombre es requerido',
                'option_type.required'=>'La opcion es requerido',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                
                return response()->json(['error'=>$validator->errors()->all()]);
            }

            $arrayData = json_decode($request->variaciones, true); 
            $arraySize = count($arrayData);
            if($arraySize == 0) {
                return response()->json([
                    'estado'=> 0,
                    'mensaje' => 'Error, No hay variaciones agregadas a la variante',
                ]);
            }

            try {
                DB::beginTransaction();
                $message = "";
                if($request->id == 0){//for add new register
                    $variacion = new Variacion();
                    $variacion->name = $request->name;
                    $variacion->option_type = $request->option_type;
                    $variacion->save();
                    foreach ($arrayData as $item) {
                        $variante = new Variantes_para_variacion();
                        $variante->variacion_id = $variacion->id;
                        $variante->name = $item;
                        $variante->save();
                    }
                    $message = "Se guardo la variacion";
                }
                if($request->id > 0){//for update register
                    $currentArray = [];
                    $id = $request->id;
                    $variacion = Variacion::find($id);
                    $variacion->name = $request->name;
                    $variacion->option_type = $request->option_type;
                    $variacion->update();

                    $getVarianteByVariacion = Variantes_para_variacion::where([ ['status', 1], ['variacion_id', $variacion->id]])->get();//get the variantes current
                    foreach ($getVarianteByVariacion as $row) {
                        array_push($currentArray, $row->name);
                    }

                    $diff = array_diff($currentArray, $arrayData);//get the difference
                    $sizeDiff = count($diff);
                    
                    if($sizeDiff > 0){
                        foreach ($diff as $down) {
                            $varianteDown = Variantes_para_variacion::where([ ['name', trim($down)], ['variacion_id', $variacion->id], ['status', 1] ])->first(); 
                            $varianteDown->status = 0;
                            $varianteDown->update();
                        }
                        if ($currentArray !== $arrayData) {//compare if two array are equals

                            $addVariacion = array_diff($arrayData, $currentArray);//Gets the new records to insert
                            $sizeNew = count($addVariacion);
                            
                            if($sizeNew > 0){//if array to  have content
                                foreach ($addVariacion as $add) {
                                    $addvariante = new Variantes_para_variacion();
                                    $addvariante->variacion_id = $variacion->id;
                                    $addvariante->name = $add;
                                    $addvariante->save();
                                }
                            }
                        }
                    }
                    if($sizeDiff == 0){
                        $addVariacion = array_diff($arrayData, $currentArray);//Gets the new records to insert
                        $sizeNew = count($addVariacion);

                        if($sizeNew > 0){//if array to  have content
                            foreach ($addVariacion as $add) {
                                $addvariante = new Variantes_para_variacion();
                                $addvariante->variacion_id = $variacion->id;
                                $addvariante->name = $add;
                                $addvariante->save();
                            }
                        }
                      
                    }
                    $message = "Se actualizo la variante";
                }
                DB::commit();
                return response()->json([
                    'estado'=> 1,  
                    'request'=>$request->option_type,
                    'name'=>$request->name,
                    'variacion'=>$request->variaciones,
                    'array'=>$arrayData,
                    'array_size'=>$arraySize,
                    'mensaje' => $message,
                    //'baseVariacion'=>$getVarianteByVariacion
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                $m = 'Excepción capturada: '.$th->getMessage(). "\n";
                return response()->json([
                    'estado'=> 0,
                    'mensaje' => (array) $m,
                ]);
            }
            
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $data = DB::table('variaciones')->where('status','=','1')->get();

        return DataTables::of($data)
        ->addColumn('action', function($data){
            $id = $data->id;
            $button ='<i class="fas fa-edit text-primary mr-3" id="" onclick="edit_variante('.$id.');" title="Actualizar la variante"></i>';
            /*$id = $data->id;
            $talla_producto = DB::table('productos')->where('marca_id',$id)->first();
            if ($talla_producto == null) {
                $button ='<i class="fas fa-edit text-primary mr-3" id="" onclick="edit_marca('.$id.');" title="Actualizar la marca"></i>';
                $button .='<i class="fas fa-trash-alt text-danger" onclick="delete_marca('.$id.');" data-toggle="tooltip" title="Eliminar la marca"></i>';
            }else{
                $button ='<i class="fas fa-edit text-primary mr-3" id="" onclick="edit_marca('.$id.');" title="Actualizar la marca"></i>';
                $button .='<i class="fas fa-trash-alt text-warning" data-toggle="tooltip" title="Esta en uso la marca"></i>';
            }
            return $button;*/
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
        $variacion = Variacion::where($where)->first();

        $getVariantes = DB::table('variantes_para_variaciones as vpv')
        ->where([
            ['vpv.variacion_id','=', $id],
            ['vpv.status','=',1]
        ])
        ->get();

        return response()->json(['variacion'=>$variacion,'variantes'=>$getVariantes]);
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
    public function destroy(string $id)
    {
        //
    }
}
