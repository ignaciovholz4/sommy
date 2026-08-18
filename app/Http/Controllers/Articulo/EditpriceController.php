<?php

namespace App\Http\Controllers\Articulo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Articulo;

class EditpriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categoria = DB::table('categorias')->where('estatus','=','1')->get();
        return view('almacen.articulo.edit_price',['categoria'=>$categoria]);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {

            $articulo = Articulo::where([
                ['categoria_id','=', $id]
            ])->get();
            
            return response()->json([
                'status'=> 1,  
                'data'=>$articulo,
                'message' => 'Articulos'
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
