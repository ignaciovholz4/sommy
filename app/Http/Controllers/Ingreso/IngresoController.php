<?php

namespace App\Http\Controllers\Ingreso;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

use App\Http\Controllers\General\FechaController;
use Illuminate\Http\Request;
use App\Models\Detalle_entrada_temp;
use App\Models\Ingreso_producto;
use App\Models\Detalle_ingreso_producto;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        Gate::authorize('haveaccess','compras_entrada.index');
        return view('compras.ingreso.index');   
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   

            $folio = $this->folioid();

        $proveedor = DB::table('proveedores')
        ->where("estado", '=', 'activo')->get();
        //dd($proveedor);
        return view('compras.ingreso.create',['proveedor'=>$proveedor,"folio"=>$folio]);
    }


    protected function folioid()
    {
        $date = new  FechaController();
        $day_now = $date->day();
        $month_now = $date->month();
        $year_now = $date->year();

        $get= DB::table('ingresos')
        ->select('idingreso')
        ->orderby('idingreso','DESC')->take(1)->get();
        $count = count($get);
        if ($count == 1) {
            $new= $get[0]->idingreso+1;
        }else{
            $new = 0;
        }
        $say = $day_now;
        return $newfolio = $say." - ".$month_now." - ".$year_now." - ".$new;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'idproveedor' => 'required',
            'folio' => 'required',
            'total_input' => 'required',
            // 'pcantidad' => 'required' 
            'idarticulo' => 'required'
        ];
        $messages = [
            'idarticulo.required' => 'El articulo es requerido',
            'idproveedor.required' => 'El nombre de proveedor es requerido',
            'folio.required' => 'El folio de la venta es requerido',
            'total_input.required' => 'El total de la venta es requerido'
            // 'pcantidad.required' => 'La cantidad es requerida',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {  
            return response()->json([
                'estado' => 'errorvalidacion',
                'mensaje'=> $validator->errors()->all(),
                'uploaded_image' => '',
                'class_name' => 'alert-danger'
            ]);
        }

        try {
            DB::beginTransaction();
            /**SE INSTANCIA LA FECHA PARA UTILIZAR*/
            $date = new  FechaController();
            $date_time = $date->fecha_hora();
            /**SE GUARDAN LOS DATOS EN LA TABLA INGRESOS EN LA DB*/
            $entradasp = new Ingreso_producto();
            $entradasp->user_id = $request->user_id;
            $entradasp->proveedor_id = $request->idproveedor;
            $entradasp->folio_comprobante = $request->folio;
            $entradasp->fecha_hora= $date_time;
            $entradasp->total_ingreso = str_replace(' ', '', $request->total_input);
            $entradasp->estado="Activo";
            $entradasp->save();
            
            /**SE GUARDAN LOS PRODUCTOS EN LA TABLA DETALLE INGRESOS EN LA DB */
            $idarticulo= $request->idarticulo;
            $cantidad= $request->cantidad;
            $pcompra= $request->pcompra;
            $pventa= $request->pventa;
            $tipoProductoId = $request->tipoProductoId;
            $variacionId = $request->variacionId;
            $subtotal=str_replace(' ', '', $request->subtotalprod);

            $cont = 0;
            while ($cont < count($idarticulo)) {
                $detalle = new Detalle_ingreso_producto();
                $detalle->ingreso_id = $entradasp->idingreso;
                $detalle->articulo_id = $idarticulo[$cont];
                $detalle->cantidad = $cantidad[$cont];
                $detalle->precio_compra = $pcompra[$cont];
                $detalle->precio_venta = $pventa[$cont];
                $detalle->subtotal = $subtotal[$cont];
                $detalle->tipo_producto_id = $tipoProductoId[$cont];
                if( (int) $variacionId[$cont] != 0){
                    $detalle->producto_variacion_variante_id = $variacionId[$cont];
                }
                $detalle->save();
                $cont=$cont+1;
                
            }
            /**UNA VEZ QUE SE GUARDARON LOS ARTICULOS SE PROCEDE A ELIMINARLOS DE LA BASE DE DATOS TEMPORAL*/
            $iduser = $request->user_id;
            $eliminar = DB::delete('delete from detalle_entrada_temp where id_user=?', [$iduser]);
            // $idproveedor = $request->idproveedor;
            //GET NEW FOLIO FOR THE INPUT
            $folio = $this->folioid();
            DB::commit();
            return response()->json([
                'estado'=> 1,  
                'mensaje' => 'Se guardo con exito la entrada de los productos',
                'newfolio' => $folio

            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                'estado'=> 0,  
                'mensaje' => (array) $m
            ]);
        }
        
            
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {

        $id  = Auth::id();

        $tipo_user = DB::table('users')
        ->join('role_user', 'users.id', '=', 'role_user.user_id')
        ->join('roles', 'roles.id', '=', 'role_user.role_id')
        ->select('roles.full-access as access')
        ->where('users.id', '=', $id)
        ->get();

        $access = $tipo_user[0]->access;

        /**SE INSTANCIA LA FECHA PARA UTILIZAR*/
        $date = new  FechaController();
        $date_now = $date->datenow();

        switch ($access) {
            case 'yes':
                $entradas = DB::table('ingresos as ing')
                ->join('proveedores as pro', 'ing.proveedor_id' ,'=', 'pro.idproveedor')
                ->select('ing.idingreso', 'ing.fecha_hora', 'pro.nombre', 'ing.folio_comprobante', 'ing.total_ingreso', 'ing.estado')
                ->where([
                    //['ing.user_id','=',$id],
                    ['ing.estado','=','Activo']
                ])
                //->whereRaw("CAST(ing.fecha_hora AS DATE) ='$date_now'")
                ->get();
            break;
            case 'no':
                $entradas = DB::table('ingresos as ing')
                ->join('proveedores as pro', 'ing.proveedor_id' ,'=', 'pro.idproveedor')
                ->select('ing.idingreso', 'ing.fecha_hora', 'pro.nombre', 'ing.folio_comprobante', 'ing.total_ingreso', 'ing.estado')
                ->where([
                    ['ing.user_id','=',$id],
                    ['ing.estado','=','Activo']
                ])
                ->whereRaw("CAST(ing.fecha_hora AS DATE) ='$date_now'")
                ->get();
            break;
            default:
            break;
        }
        /*$getventas = DB::table('ventas')->where([
            ['user_id','=',$id],
            ['estado','=','Activo'],
        ])
        ->whereRaw("CAST(fecha_hora AS DATE) ='$now'")
        ->get();*/

        return DataTables::of($entradas)
        ->addColumn('action', function($entradas){
            $id = $entradas->idingreso;
            $button = '<button type="button" class="btn btn-secondary btn-sm btn-flat" onclick="show_product_entrada('.$id.');" ><i class="fa fa-eye"></i></button> &nbsp;&nbsp;&nbsp;';
            //$button .= '<button type="button" class="btn btn-danger btn-sm" onclick="delete_product('.$id.');">sin</button>';
            return $button;
        })
        ->rawColumns(['action'])
        ->make(true);
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function find_nombre(Request $request)
    {
        try {
            
            $nombre = trim($request->valor);

            $producto = DB::table('productos')
            ->where('nombre','like','%'.$nombre.'%')
            ->orwhere('codigo','like','%'.$nombre.'%')
            ->limit(5)
            ->get();
            $array_tags = [];
            foreach ($producto as $tag) {
                $id = $tag->idarticulo;
                $codigo = $tag->codigo;
                $nombre = $tag->nombre;
                $compra = $tag->pcompra;
                $venta = $tag->pventa; 
                $tipo_producto_id = $tag->tipo_producto_id; 
                $img = "/imagenes/articulos/".$tag->imagen;

                $findVariantes = [];
                if($tipo_producto_id == 2){
                    $findVariantes = DB::table('productos as p')
                    ->join('producto_integracion_variante as piv', 'p.idarticulo', '=', 'piv.producto_id')
                    ->join('producto_variacion_variante as pvv', 'piv.id', '=', 'pvv.product_integration_id')
                    ->join('variantes_para_variaciones as vpv', 'piv.variante_id', '=', 'vpv.id')
                    ->join('color as c', 'pvv.color_id', '=', 'c.id')
                    ->select('vpv.id as varianteVariacionId','vpv.name as nameVariante','c.name as nameColor','pvv.*')
                    ->where([
                        ['p.idarticulo', '=', $id],
                        ['p.tipo_producto_id', '=', 2],
                        ['pvv.active', '=', 1]
                    ])
                    ->get();
                }

                $array_tags[]=['id'=>$id,'codigo'=>$codigo,
                'nombre'=>$nombre,'img'=>$img,'pcompra'=>$compra,'pventa'=>$venta,
                'tipo_producto_id' => $tipo_producto_id,
                'productVariante' => $findVariantes];
            }

            return response()->json([
                "estado" => 1,
                "getprodent"=>$array_tags
            ]);
            //return \Response::json($array_tags);

        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                "estado" => 0,
                "mensaje"=> (array) $m
            ]);
        }




    }

    public function save_temp(Request $request)
    {

        $rules = [
            'idarticulo' => 'required',
            'IngresoCodigoArticulo' => 'required',
            'pnombrearticulo' => 'required',
            'pcantidad' => 'required',
            'pprecio_compra' => 'required',
            'pprecio_venta' => 'required',
            'tipoProductoId' => 'required',
            'selectElementVariant' => ['required_if:tipoProductoId,2'],
            'selectElementVariantTipo' => ['required_if:tipoProductoId,2'],
        ];
        $messages = [
            'idarticulo.required' => 'El id del articulo es requerido',
            'IngresoCodigoArticulo.required' => 'El codigo es requerido',
            'pnombrearticulo.required' => 'El nombre del articulo es requerido',
            'pcantidad.required' => 'La cantidad es requerida',
            'pprecio_compra.required' => 'El precio de compra es requerido',
            'pprecio_venta.required' => 'El precio de venta es requerido',
            'tipoProductoId.required' => 'El tipo de producto es requerido',
            'selectElementVariant.required_if' => 'La variante es requerida',
            'selectElementVariantTipo.required_if' => 'La variacion es requerida',
            
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {  
            return response()->json([
                'estado' => 'errorvalidacion',
                'mensaje'=> $validator->errors()->all(),
                'uploaded_image' => '',
                'class_name' => 'alert-danger'
            ]);
        }

        try {
        
        $id_user_tmp = $request->id_user;
        $idarticulo_tmp = $request->idarticulo;
        $codigo_tmp = $request->IngresoCodigoArticulo;
        $nombre_tmp = $request->pnombrearticulo;
        $cantidad_tmp = $request->pcantidad;
        $pcompra_tmp = $request->pprecio_compra;
        $pventa_tmp = $request->pprecio_venta;

        $tipoProductoId = $request->tipoProductoId;
        $producto_variacion_variante_id = 0;
        if ($tipoProductoId == 2) $producto_variacion_variante_id = $request->selectElementVariantTipo;
        // return response()->json([
        //     'iduser'=> $id_user,
        //     'idarticulo'=> $idarticulo,
        //     'codigo'=> $codigo,
        //     'nombre'=> $nombre,
        //     'cantidad'=> $cantidad,
        //     'compra'=> $pcompra,
        //     'venta'=> $pventa,
        // ]);
        $datos = DB::select("call add_detalle_entrada_tmp($id_user_tmp,$idarticulo_tmp,'$codigo_tmp','$nombre_tmp',$cantidad_tmp,$pcompra_tmp,$pventa_tmp,$tipoProductoId,$producto_variacion_variante_id)");
        $total = 0;
        $array_productos_tmp = [];
        $array_variante = [];
        foreach ($datos as $productos) {
            $idtemp = $productos->identradatemp;
            $id_art = $productos->idarticulo;
            $codigo = $productos->codigo;
            $nombre = $productos->nombre;
            $cantidad = number_format($productos->total_articulos, 2, '.', ' ');
            $compra = $productos->pcompra;
            $venta = $productos->pventa;
            $subtotal_prod = round($productos->total_articulos * $productos->pcompra,2);
            $subtotal_format = number_format($subtotal_prod, 2, '.', ' ');
            $total = round($total + $subtotal_prod,2);

            $tipo_producto_id = $productos->tipo_producto_id;
            $producto_variacion_variante_id = $productos->producto_variacion_variante_id;
            if($tipo_producto_id == 2){
            $array_variante = DB::table('producto_variacion_variante as pvv')
            ->join('color as c', 'pvv.color_id', '=', 'c.id')
            ->join('producto_integracion_variante as piv', 'pvv.product_integration_id', '=', 'piv.id')
            ->join('variantes_para_variaciones as vpv', 'piv.variante_id', '=', 'vpv.id')
            ->select('vpv.name as name_variante', 'c.name as name_color', 'pvv.*')
            ->where('pvv.id', $producto_variacion_variante_id)
            ->first();
            }

            $array_productos_tmp[] = ["idtemp"=>$idtemp,"idarticulo"=>$id_art,"codigo"=>$codigo,"nombre"=>$nombre,"cantidad"=>$cantidad,
            "pcompra"=>$compra,"pventa"=>$venta,"subtotalprod"=>$subtotal_prod,"subtotal_format"=>$subtotal_format,
            "tipo_producto_id" => $tipo_producto_id, "producto_variacion_variante_id" => $producto_variacion_variante_id, "dataVariante" => $array_variante];
        }

        $total_number = number_format($total, 2, '.', ' ');
        $array_total = [];
        $array_total = ["total"=>$total_number];

        if ($datos) {
            return response()->json([
                "estado" => 1,
                "productos" => $array_productos_tmp,
                "total" => $array_total
            ]);
        }
        

        } catch (\Throwable $th) {
            // $m = $th->getMessage(), "\n";
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            //$array_error = [];
            //$array_error = ["mm"=>"Exepción capturada: ocurrio un error, vuelve a intentarlo"];
            return response()->json([
                "estado" => 0,
                "mensaje"=> (array) $m
            ]);
        }
       
    }

    public function delete_prod(Request $request)
    {
        $id_user_tmp = $request->id_user;
        // echo $id_user;
        $idart_tmp = $request->idprod;
        $id_articulo = $request->idArticulo;
        $tipo_producto_id = $request->tipo_producto_id;
        $producto_variacion_variante_id = $request->producto_variacion_variante_id;

        $delete_prod = DB::select("call delete_prod_entrada_tmp($idart_tmp,$id_user_tmp,$id_articulo,$tipo_producto_id,$producto_variacion_variante_id)");
        $total = 0;
        $array_productos_tmp = [];
        $array_variante = [];
        foreach ($delete_prod as $productos) {
            $idtemp = $productos->identradatemp;
            $id_art = $productos->idarticulo;
            $codigo = $productos->codigo;
            $nombre = $productos->nombre;
            $cantidad = $productos->total_articulos ;
            $compra = $productos->pcompra;
            $venta = $productos->pventa;
            $subtotal_prod = round($productos->total_articulos  * $productos->pcompra,2);
            $subtotal_format = number_format($subtotal_prod, 2, '.', ' ');
            $total = round($total + $subtotal_prod,2);

            $tipo_producto_id = $productos->tipo_producto_id;
            $producto_variacion_variante_id = $productos->producto_variacion_variante_id;
            if($tipo_producto_id == 2){
            $array_variante = DB::table('producto_variacion_variante as pvv')
            ->join('color as c', 'pvv.color_id', '=', 'c.id')
            ->join('producto_integracion_variante as piv', 'pvv.product_integration_id', '=', 'piv.id')
            ->join('variantes_para_variaciones as vpv', 'piv.variante_id', '=', 'vpv.id')
            ->select('vpv.name as name_variante', 'c.name as name_color', 'pvv.*')
            ->where('pvv.id', $producto_variacion_variante_id)
            ->first();
            }

            $array_productos_tmp[] = ["idtemp"=>$idtemp,"idarticulo"=>$id_art,"codigo"=>$codigo,"nombre"=>$nombre,"cantidad"=>$cantidad,
            "pcompra"=>$compra,"pventa"=>$venta,"subtotalprod"=>$subtotal_prod,"subtotal_format"=>$subtotal_format,
            "tipo_producto_id" => $tipo_producto_id, "producto_variacion_variante_id" => $producto_variacion_variante_id, "dataVariante" => $array_variante];
        }

        $total_number = number_format($total, 2, '.', ' ');
        $array_total = [];
        $array_total = ["total"=>$total_number];

        return response()->json([
            "estado" => "1",
            "productos" => $array_productos_tmp,
            "total" => $array_total
        ]);

    }

    public function show_prod(Request $request)
    {
        $id_user = $request->id_user;

        $showproductos = DB::table('detalle_entrada_temp as det')
        //->select('*',DB::raw('SUM(cantidad) as total_articulos')) 
        ->select('det.*') 
        ->where('det.id_user','=',$id_user)
        //->groupBy('idarticulo')
        ->orderByDesc('identradatemp')
        ->get();
        $total = 0;
        $array_productos_tmp = [];
        $array_variante = [];
        foreach ($showproductos as $productos) {
            $idtemp = $productos->identradatemp;
            $id_art = $productos->idarticulo;
            $codigo = $productos->codigo;
            $nombre = $productos->nombre;
            $cantidad = number_format($productos->cantidad, 2, '.', ' ');
            $compra = $productos->pcompra;
            $venta = $productos->pventa;
            $subtotal_prod = round($productos->cantidad * $productos->pcompra,2);
            $subtotal_format = number_format($subtotal_prod, 2, '.', ' ');
            $total = round($total + $subtotal_prod,2);
            $tipo_producto_id = $productos->tipo_producto_id;
            $producto_variacion_variante_id = $productos->producto_variacion_variante_id;
            if($tipo_producto_id == 2){
            $array_variante = DB::table('producto_variacion_variante as pvv')
            ->join('color as c', 'pvv.color_id', '=', 'c.id')
            ->join('producto_integracion_variante as piv', 'pvv.product_integration_id', '=', 'piv.id')
            ->join('variantes_para_variaciones as vpv', 'piv.variante_id', '=', 'vpv.id')
            ->select('vpv.name as name_variante', 'c.name as name_color', 'pvv.*')
            ->where('pvv.id', $producto_variacion_variante_id)
            ->first();
            }
            $array_productos_tmp[] = ["idtemp"=>$idtemp,"idarticulo"=>$id_art,"codigo"=>$codigo,"nombre"=>$nombre,"cantidad"=>$cantidad,
            "pcompra"=>$compra,"pventa"=>$venta,"subtotalprod"=>$subtotal_prod, "subtotal_format"=>$subtotal_format,
            "tipo_producto_id" => $tipo_producto_id, "producto_variacion_variante_id" => $producto_variacion_variante_id, "dataVariante" => $array_variante];
        }
        
        $total_number = number_format($total, 2, '.', ' ');

        $array_total = [];
        $array_total = ["total"=>$total_number];

        return response()->json([
            "estado" => "1",
            "productos" => $array_productos_tmp,
            "total" => $array_total
        ]);

    }

    public function get_products($id)
    { 
        $getdatos = DB::table('ingresos as ing')
        ->join('proveedores as pro', 'ing.proveedor_id','=','pro.idproveedor')
        //->join('detalle_ingresos as di', 'ing.idingreso','=','di.ingreso_id')
        ->select('pro.nombre','ing.folio_comprobante','ing.fecha_hora','ing.total_ingreso')
        ->where('ing.idingreso','=',$id)
        ->first();
        $getproductos = DB::table('detalle_ingresos as di')
        ->join('productos as p', 'di.articulo_id','=','p.idarticulo')
        ->select('p.nombre','di.cantidad','di.precio_compra','di.subtotal','di.tipo_producto_id','di.producto_variacion_variante_id')
        ->where('di.ingreso_id','=',$id)
        ->get()
        ->map(function ($producto) {
            $array_variante = [];
            if($producto->tipo_producto_id == 2){
            $array_variante = DB::table('producto_variacion_variante as pvv')
            ->join('color as c', 'pvv.color_id', '=', 'c.id')
            ->join('producto_integracion_variante as piv', 'pvv.product_integration_id', '=', 'piv.id')
            ->join('variantes_para_variaciones as vpv', 'piv.variante_id', '=', 'vpv.id')
            ->select('vpv.name as name_variante', 'c.name as name_color')
            ->where('pvv.id', $producto->producto_variacion_variante_id)
            ->first();
            }
            return [
                'nombre' => $producto->nombre,
                'cantidad' => $producto->cantidad,
                'precio_compra' => $producto->precio_compra,
                'subtotal' => $producto->subtotal,
                'dataVariante' => $array_variante,
                'tipo_producto_id' => $producto->tipo_producto_id,
            ];
        });


        return response()->json([
            "exito"=>"se envio con exito",
            'datos'=>$getdatos,
            'productos'=>$getproductos
        ]);
    }

    public function searh_proveedores(Request $request)
    {

        $proveedor = $request->valor;

        $proveedores= DB::table('proveedores')
        ->where('nombre','like','%'.$proveedor.'%')
        ->get();

            $array_pro = [];
            foreach ($proveedores as $tag) {
                $id = $tag->idproveedor;
                $nombre = $tag->nombre;
                $array_pro[]=['id'=>$id,'nombre'=>$nombre];
            }
        
        return response()->json([
            'alldata' => $array_pro,
            'modulo' => 'proveedor'
        ]);
    }


}
