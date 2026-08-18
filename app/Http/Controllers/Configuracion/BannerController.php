<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

use App\Models\configuracion\Banner;

use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.banner.index');
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

            if((int)$request->bannerId === 0){
                $rules = [
                    'name' => 'required',
                    'imagen' => 'required|image',
                    'imageMovil' => 'required|image'
                ];

                $messages = [
                    'name.required'=>'El nombre es requerido',
                    'imagen.required'=>'La imagen es requerida',
                    'imagen.image' => 'Debe de agregar una imagen para escritorio',
                    'imageMovil.required'=>'La imagen para movil es requerida',
                    'imageMovil.image' => 'Debe de agregar una imagen para movil',
                ];
            }else{
                $rules = [
                    'name' => 'required',
                ];

                $messages = [
                    'name.required'=>'El nombre es requerido',
                ];
            }

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'=> 0,
                    'message' => $validator->errors()->all()
                ]);
            }

            $message = "";
            if((int)$request->bannerId === 0){//for new
                $banner = new Banner();
                $banner->name = $request->name;
                $files = $request->file('imagen');
                $filesMovil = $request->file('imageMovil');
                if ($files && $filesMovil) {
                    $destinationPath = public_path('/imagenes/banner');
                    $nameImagen = trim($files->getClientOriginalName());
                    $nameImagenMovil = trim($filesMovil->getClientOriginalName());
                    $countNameImage = DB::table('banner_ecommerce')
                    ->where('status', true)
                    ->where(function($query) use ($nameImagen, $nameImagenMovil) {
                        $query->where('name_image', $nameImagen)
                            ->orWhere('name_image_movil', $nameImagenMovil);
                    })
                    ->count();
                    /*$countNameImage =  DB::table('banner_ecommerce')
                    ->where([
                        ['status', '=', true],
                        ['name_image', '=', $nameImagen],
                    ])->count();*/

                    if($countNameImage > 0){
                        return response()->json([
                            'status'=> 0,
                            'message' => (array) "Ya existe una imagen con el mismo nombre.",
                        ]);
                    }
                    $files->move($destinationPath, $nameImagen);
                    $banner->name_image = $nameImagen;
                    $filesMovil->move($destinationPath, $nameImagenMovil);
                    $banner->name_image_movil = $nameImagenMovil;
                }
                $banner->save();
                $message = 'Se guardo con exito el banner';
            }
            if((int)$request->bannerId > 0){//for update
                $getBanner = Banner::where('banner_id', (int)$request->bannerId)->first();
                if($getBanner){
                    $destinationPath = public_path('/imagenes/banner');
                    $files = $request->file('imagen');
                    $filesMovil = $request->file('imageMovil');
                    if ($files && $files !== null) {
                        $nameImagen = trim($files->getClientOriginalName());
                        $countNameImage =  DB::table('banner_ecommerce')
                        ->where([
                            ['status', '=', true],
                            ['name_image', '=', $nameImagen],
                        ])->count();

                        if($countNameImage > 0){
                            return response()->json([
                                'status'=> 0,
                                'message' => (array) "Ya existe una imagen para desktop con el mismo nombre.",
                            ]);
                        }

                        if (File::exists(public_path('/imagenes/banner/'.$getBanner->name_image))) {
                            File::delete(public_path('/imagenes/banner/'.$getBanner->name_image));
                        }
                        $files->move($destinationPath, $nameImagen);
                        $getBanner->name_image = $nameImagen;
                    }
                    if ($filesMovil && $filesMovil !== null) {
                        $nameImagenMovil = trim($filesMovil->getClientOriginalName());
                        $countNameImage =  DB::table('banner_ecommerce')
                        ->where([
                            ['status', '=', true],
                            ['name_image_movil', '=', $nameImagenMovil],
                        ])->count();

                        if($countNameImage > 0){
                            return response()->json([
                                'status'=> 0,
                                'message' => (array) "Ya existe una imagen para movil con el mismo nombre.",
                            ]);
                        }

                        if (File::exists(public_path('/imagenes/banner/'.$getBanner->name_image_movil))) {
                            File::delete(public_path('/imagenes/banner/'.$getBanner->name_image_movil));
                        }
                        $filesMovil->move($destinationPath, $nameImagenMovil);
                        $getBanner->name_image_movil = $nameImagenMovil;
                    }
                    $getBanner->update();
                    $message = 'Se actualizo con exito el banner';
                }
            }

            return response()->json([
                'status'=> 1,
                'message' => $message,
            ]);
        } catch (\Throwable $th) {
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
    public function show()
    {
        $data = DB::table('banner_ecommerce')->where('status','=',1)->get();

        return DataTables::of($data)
        ->addColumn('action', function($data){
            $id = $data->banner_id;
            $button ='<i class="fas fa-edit text-primary mr-3" id="" onclick="edit_banner('.$id.');" title="Actualizar la categoria"></i>';
            $button .='<i class="fas fa-trash-alt text-danger" onclick="delete_banner('.$id.');" data-toggle="tooltip" title="Eliminar la categoria"></i>';
            return $button;
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        try {

            $getByIdBanner = DB::table('banner_ecommerce as be')->where('banner_id','=', $request->id)->get();

            return response()->json([
                'id' => $request->id,
                'data' => $getByIdBanner
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
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
            $message = "";
            $idbanner = $request->bannerId;
            $banner = Banner::where('banner_id', (int)$request->bannerId)->first();
            //$banner = Banner::where('banner_id', $request->bannerId)->update(['status' => 0]);
            if($banner){
                if(File::exists(public_path('/imagenes/banner/'.$banner->name_image))) {
                    File::delete(public_path('/imagenes/banner/'.$banner->name_image));
                    $banner->status=0;
                    $banner->update();
                    $message = "Se elimino el banner con exito";
                }
            }

            return response()->json([
                'status'=> 1,  
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                'status'=> 0,  
                'message' => (array) $m
                //'message' => 'Error: No se pudo eliminar intentalo de nuevo'
            ]);
        }
    }
}
