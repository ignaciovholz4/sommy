<?php

namespace App\Http\Controllers\Variaciones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use App\Models\variacion\Producto_variante;
use App\Models\variacion\Producto_integracion_variante;
use App\Models\Articulo;

class ProductovarianteController extends Controller
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
        try {
            $rules = [
                'selectcolor' => 'required',
                'productintegrationid' => 'required',
                'priceVariante' => 'required',
                'priceCompraVariante' => 'required',
                'photovariante' => 'required|file|mimes:jpg,jpeg,png|max:2048',
                'productId' => 'required',
                'stockVariant' => 'required'
            ];

            $messages = [
                'selectcolor.required'=>'El color es requerido',
                'productintegrationid.required'=>'El identificador de la integracion es requerido',
                'priceVariante.required'=>'El precio de venta es requerido',
                'priceCompraVariante.required'=>'El precio de compra es requerido',
                'photovariante.required'=>'La imagen es requerida',
                'productId.required'=>'El identificador del producto es requerida',
                'stockVariant.required' => 'El stock es requerido'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'=> 0,
                    'message' => $validator->errors()->all(),
                ]);
            }

            $findVarianter = DB::table('producto_integracion_variante as piv')
            ->join('producto_variacion_variante as pvv', 'piv.id', '=', 'pvv.product_integration_id')
            ->select('piv.id as productoIntegracionVariante','pvv.id as productoVariacionVarianteId')
            ->where(
                [
                    ['pvv.color_id', '=', $request->selectcolor],
                    ['piv.producto_id', '=', $request->productId],
                    ['pvv.product_integration_id', '=', $request->productintegrationid],
                    ['piv.activo', '=', 1],
                    ['pvv.active', '=', 1],
                ]
            )
            ->first();
            /*$findVarianter = Producto_variante::where([
                ['product_integration_id', '=', $request->productintegrationid],
                ['color_id', '=', $request->selectcolor],
                ['active', '=', 1],
                ['show_ecommerce', '=', 0],
            ])->first();*/
            if($findVarianter){
                return response()->json([
                    'status'=> 0,
                    'message' => (array) 'Ya existe la combinacion para esta Variante',
                ]);
            }
            DB::beginTransaction();

            $fileName = "";
            $messageFolder = "";
            $pathImage = 'imagenes/articulo_variante/product-'.$request->productId;
            $directoryPath = public_path('imagenes/articulo_variante/product-'.$request->productId);
            if ($request->hasFile('photovariante')) {
                if (!File::exists($directoryPath)) {
                    if (File::makeDirectory($directoryPath, 0755, true)) {//create folder for save images
                        $messageFolder = "created successfully in the director";
                        $file = $request->file('photovariante');
                        $fileName = $file->getClientOriginalName();
                        $file->move($directoryPath, $fileName);
                    }
                }else{
                    $messageFolder = "folder already exists";
                    $file = $request->file('photovariante');
                    $fileName = $file->getClientOriginalName();
                    $file->move($directoryPath, $fileName);
                }
            }

            $prodVariante = new Producto_variante();
            $prodVariante->color_id = $request->selectcolor;
            $prodVariante->product_integration_id = $request->productintegrationid;
            $prodVariante->price = $request->priceVariante;
            $prodVariante->name_image = $fileName;
            $prodVariante->path_image = $pathImage;
            $prodVariante->stock = $request->stockVariant;
            $prodVariante->pcompra = $request->priceCompraVariante;

            $prodVariante->save();
            /***********UPDATE STOCK*******************/
            $find = Producto_integracion_variante::find($request->productintegrationid);
            $findProduct = Articulo::find($find->producto_id);
            $findProduct->stock = $findProduct->stock + $request->stockVariant;
            $findProduct->save();

            $findVariacionProduct = DB::table('producto_integracion_variante as piv')
            ->join('producto_variacion_variante as pvv', 'piv.id', '=', 'pvv.product_integration_id')
            ->select('piv.variacion_id')
            ->where(
                [
                    ['pvv.product_integration_id', '=', $prodVariante->product_integration_id],
                    ['piv.activo', '=', 1],
                    ['pvv.active', '=', 1],
                ]
            )
            ->first();

            DB::commit();
            return response()->json([
                'status'=> 1,
                'data'=> '',
                'message' => (array) 'Exito. Se guardo correctamente.',
                'folder' => $messageFolder,
                'path' => $directoryPath,
                'rowData' => $prodVariante,
                'findVariacionProduct' => $findVariacionProduct
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
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $findVariacionProduct;
            $textImage = "";
            $varianteId = $request->id;
            $prodVariant = Producto_variante::where('id', $varianteId)->first();
            if($prodVariant){
                $prodVariant->price = $request->upPrecio;
                //$prodVariant->stock = $request->upStock;
                if ($request->hasFile('upImage')) {
                    if (File::exists(public_path('/imagenes/articulo_variante/product-'.$varianteId.'/'.$prodVariant->name_image))) {
                        File::delete(public_path('/imagenes/articulo_variante/product-'.$varianteId.'/'.$prodVariant->name_image));
                    }
                    $files = $request->file('upImage');
                    $destinationPath = public_path($prodVariant->path_image);
                    $nameImagen = trim($files->getClientOriginalName());
                    $files->move($destinationPath, $nameImagen);
                    $prodVariant->name_image = $nameImagen;
                    $textImage = "Se actualizo con exito la imagen";
                }
                $prodVariant->save();
                /***************************/
                $findVariacionProduct = DB::table('producto_integracion_variante as piv')
                ->join('producto_variacion_variante as pvv', 'piv.id', '=', 'pvv.product_integration_id')
                ->select('piv.variacion_id')
                ->where(
                    [
                        ['pvv.product_integration_id', '=', $prodVariant->product_integration_id],
                        ['piv.activo', '=', 1],
                    ]
                )
                ->first();
            }

            return response()->json([
                "status" => 1,
                "fileMessage" => $textImage,
                'findVariacionProduct' => $findVariacionProduct,
                'message' => 'Se actualizo la variante con exito',
            ]);
        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                "status" => 0,
                'message' => (array) $m,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $varianteId = $request->varianteId;
            $productVariant = Producto_variante::where(
            [
                ['id', '=', $varianteId],
                ['show_ecommerce', '=', 0]
            ]
            )->first();

            if($productVariant === null){
                return response()->json([
                    "status" => 0,
                    'message' => (array) 'No se puede eliminar por que esta activo en la seccion mostrar en ecommerce',
                ]);
            }
            $findVariacionProduct;
            if ($productVariant) {
                $productVariant->update(['active' => 0]);
                $findVariacionProduct = DB::table('producto_integracion_variante as piv')
                ->join('producto_variacion_variante as pvv', 'piv.id', '=', 'pvv.product_integration_id')
                ->select('piv.variacion_id')
                ->where(
                    [
                        ['pvv.product_integration_id', '=', $productVariant->product_integration_id],
                        ['piv.activo', '=', 1],
                    ]
                )
                ->first();
            }

            return response()->json([
                'status' => 1,
                'message' => 'Se elimino la variante con exito',
                'findVariacionProduct' => $findVariacionProduct
            ]);
        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                "status" => 0,
                'message' => (array) $m,
            ]);
        }
    }

    public function updateRow(Request $request)
    {
        try {

            $findActiveData = DB::table('producto_integracion_variante as piv')
            ->join('producto_variacion_variante as pvv', 'piv.id', '=', 'pvv.product_integration_id')
            ->select('pvv.id','pvv.show_ecommerce')
            ->where(
                [
                    ['piv.producto_id', '=', $request->productId],
                    ['piv.activo', '=', 1],
                    ['pvv.active', '=', 1],
                    ['pvv.show_ecommerce', '=', 1],
                ]
            )
            ->first();

            if($findActiveData){
                $prodDown = Producto_variante::where('id', $findActiveData->id)->first();
                $prodDown->show_ecommerce = 0;
                $prodDown->save();
            }

            $id = $request->id;
            $prodVariant = Producto_variante::where('id', $id)->first();
            if($prodVariant){
                $prodVariant->show_ecommerce = 1;
                $prodVariant->save();
            }

            return response()->json([
                "status" => 1,
                "message" => "Se activo con exito la variante para el producto",
                //"data" => $findActiveData,
                //"prod" => $request->productId
            ]);
        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                "status" => 0,
                'message' => (array) $m,
            ]);
        }

    }

    public function addMoreStockVariant(Request $request) 
    {
        try {

            $rules = [
                'variantIdStockProduct' => 'required',
                'inputNewStockVariant' => 'required',
            ];

            $messages = [
                'variantIdStockProduct.required'=>'El id es requerido',
                'inputNewStockVariant.required' => 'El stock es requerido',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'=> 0,
                    'message' => $validator->errors()->all(),
                ]);
            }

            DB::beginTransaction();

            $findVariacionProduct;
            $prodVariant = Producto_variante::where('id', $request->variantIdStockProduct)->first();
            if($prodVariant){
                $sumaStock = $prodVariant->stock + $request->inputNewStockVariant;
                $prodVariant->stock = $sumaStock;
                $prodVariant->save();
                /***********UPDATE STOCK*******************/
                $find = Producto_integracion_variante::find($prodVariant->product_integration_id);
                $findProduct = Articulo::find($find->producto_id);
                $findProduct->stock = $findProduct->stock + $request->inputNewStockVariant;
                $findProduct->save();
                /***************************/
                $findVariacionProduct = DB::table('producto_integracion_variante as piv')
                ->join('producto_variacion_variante as pvv', 'piv.id', '=', 'pvv.product_integration_id')
                ->select('piv.variacion_id')
                ->where(
                    [
                        ['pvv.product_integration_id', '=', $prodVariant->product_integration_id],
                        ['piv.activo', '=', 1],
                    ]
                )
                ->first();
            }
            
            DB::commit();

            return response()->json([
                "status" => 1,
                "message" => "Exito: Se agrego mas stock a la variante",
                'findVariacionProduct' => $findVariacionProduct
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                "status" => 0,
                'message' => (array) $m,
            ]);
        }
    }
}
