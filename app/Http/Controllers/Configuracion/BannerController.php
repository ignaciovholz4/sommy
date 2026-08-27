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

            $esNuevo = (int) $request->bannerId === 0;

            $rules = [
                'name' => 'required',
                'titulo' => 'nullable|string|max:120',
                'subtitulo' => 'nullable|string|max:200',
                'boton_texto' => 'nullable|string|max:40',
                'boton_url' => 'nullable|string|max:255',
                'orden' => 'nullable|integer',
                'imagen' => ($esNuevo ? 'required' : 'nullable') . '|image|max:5120',
                'imageMovil' => 'nullable|image|max:5120',
            ];

            $messages = [
                'name.required' => 'El nombre es requerido',
                'imagen.required' => 'La imagen es requerida',
                'imagen.image' => 'Debe de agregar una imagen para escritorio',
                'imageMovil.image' => 'Debe de agregar una imagen para móvil',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status'=> 0,
                    'message' => $validator->errors()->all()
                ]);
            }

            $destinationPath = public_path('/imagenes/banner');
            $message = "";

            $datosContenido = [
                'name' => $request->name,
                'tipo' => 'imagen',
                'titulo' => $request->titulo ?: null,
                'subtitulo' => $request->subtitulo ?: null,
                'boton_texto' => $request->boton_texto ?: null,
                'boton_url' => $request->boton_url ?: null,
                'orden' => $request->orden ?: 0,
            ];

            if ($esNuevo) {
                $banner = new Banner();
                $banner->fill($datosContenido);

                $files = $request->file('imagen');
                $filesMovil = $request->file('imageMovil');

                $nameImagen = $files->hashName();
                $files->move($destinationPath, $nameImagen);
                $banner->name_image = $nameImagen;

                if ($filesMovil) {
                    $nameImagenMovil = $filesMovil->hashName();
                    $filesMovil->move($destinationPath, $nameImagenMovil);
                    $banner->name_image_movil = $nameImagenMovil;
                } else {
                    // Sin imagen móvil propia: se reutiliza la de escritorio.
                    $banner->name_image_movil = $nameImagen;
                }

                $banner->save();
                $message = 'Se guardó con éxito el banner';
            } else {
                $getBanner = Banner::where('banner_id', (int) $request->bannerId)->first();
                if ($getBanner) {
                    $getBanner->fill($datosContenido);

                    $files = $request->file('imagen');
                    $filesMovil = $request->file('imageMovil');

                    if ($files) {
                        if (File::exists(public_path('/imagenes/banner/' . $getBanner->name_image))) {
                            File::delete(public_path('/imagenes/banner/' . $getBanner->name_image));
                        }
                        $nameImagen = $files->hashName();
                        $files->move($destinationPath, $nameImagen);
                        $getBanner->name_image = $nameImagen;
                    }

                    if ($filesMovil) {
                        if (File::exists(public_path('/imagenes/banner/' . $getBanner->name_image_movil))) {
                            File::delete(public_path('/imagenes/banner/' . $getBanner->name_image_movil));
                        }
                        $nameImagenMovil = $filesMovil->hashName();
                        $filesMovil->move($destinationPath, $nameImagenMovil);
                        $getBanner->name_image_movil = $nameImagenMovil;
                    }

                    $getBanner->save();
                    $message = 'Se actualizó con éxito el banner';
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
        $data = DB::table('banner_ecommerce')->where('status','=',1)->orderBy('orden')->orderBy('banner_id')->get();

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

            $getByIdBanner = DB::table('banner_ecommerce')->where('banner_id','=', $request->id)->get();

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
            if($banner){
                if (File::exists(public_path('/imagenes/banner/'.$banner->name_image))) {
                    File::delete(public_path('/imagenes/banner/'.$banner->name_image));
                }
                if ($banner->name_image_movil && $banner->name_image_movil !== $banner->name_image
                    && File::exists(public_path('/imagenes/banner/'.$banner->name_image_movil))) {
                    File::delete(public_path('/imagenes/banner/'.$banner->name_image_movil));
                }
                $banner->status = 0;
                $banner->save();
                $message = "Se eliminó el banner con éxito";
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
