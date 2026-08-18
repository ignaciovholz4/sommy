<?php

namespace App\Http\Controllers\Variaciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

use App\Models\variacion\Producto_integracion_variante;

class ProductointegracionvarianteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        dd($request);
        try {

            $rules = [
                'idproducto' => 'required',
                'variantes' => 'required',
            ];

            $messages = [
                'idproducto.required'=>'El id producto es requerido',
                'variantes.required'=>'La variante es requerida',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'=> 0,
                    'message' => 'Ocurrio un error, Hay campos del formulario vacios',
                    'error' => $validator->errors()->all()
                ]);
            }

            $producto = $request->idproducto;

            $resultsCountVariacion = Producto_integracion_variante::select('variacion_id')
            ->where([
                ['producto_id', '=', $producto],
                ['variacion_id', '!=', $request->selectvariacion],
                ['activo', '=', 1],
            ])
            ->distinct()
            ->count('variacion_id');
            
            if($resultsCountVariacion > 0){
                return response()->json([
                    'status'=> 0,
                    'message' => 'Error, ya existe una variacion agregada al producto',
                ]);
            }

            $arrayExistingVariant = json_decode($request->variantesExistingProduct);
            $arrayVarianteExistingSize = count($arrayExistingVariant);
            $arrayData = json_decode($request->variantes, true); 
            $arraySize = count($arrayData);
            if($arraySize == 0){
                return response()->json([
                    'status'=> 0,
                    'message' => 'Error, No hay una variante agregada',
                ]);
            }
            DB::beginTransaction();
            foreach ($arrayData as $item) {
                $findIntegration = Producto_integracion_variante::where([
                    ['producto_id', '=', $producto],
                    ['variacion_id', '=', $request->selectvariacion],
                    ['variante_id', '=', $item],
                    ['activo', '=', 1],
                ])->first();
                if(!$findIntegration){
                    $prodVariante = new Producto_integracion_variante();
                    $prodVariante->producto_id = $producto;
                    $prodVariante->variacion_id = $request->selectvariacion;
                    $prodVariante->variante_id = $item;
                    $prodVariante->status = 'P';//pendiente
                    $prodVariante->save();
                }
            }

            $currentData = DB::table('producto_integracion_variante as pvv')
            ->join('variantes_para_variaciones as vpv', 'pvv.variante_id', '=', 'vpv.id' )
            ->select('pvv.*', 'vpv.name')
            ->where([['pvv.activo','=',1], ['pvv.producto_id','=', $producto], ['pvv.variacion_id','=',$request->selectvariacion]])->get();

            $color = DB::table('color')->where('status','=',1)->get();

            $dataVariant = [];
            foreach ($currentData as $row){
                $productVarainte = DB::table('producto_integracion_variante as pvv')
                ->join('producto_variacion_variante as prv', 'pvv.id', '=', 'prv.product_integration_id' )
                ->select('prv.*')
                ->where([['prv.active','=',1], ['pvv.producto_id','=', $producto],['prv.product_integration_id','=', $row->id], ['pvv.variacion_id','=',$request->selectvariacion]])
                ->get();
                $dataVariant[$row->name] = $productVarainte;
            }

            DB::commit();
            return response()->json([
                'status'=> 1,
                'getproductIntegration'=> $currentData,
                'color'=> $color,
                'getproductVariant'=> $dataVariant,
                'message' => 'Exito, Se agregaron las variantes',
                'id'=> $request->variantes,
                'arrayExistingVariant' => $arrayExistingVariant,
                'countVariantes' => $resultsCountVariacion
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                'status'=> 0,
                'message' => (array) $m,
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
