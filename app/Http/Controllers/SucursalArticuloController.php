<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\Articulo;
use App\Models\SucursalArticulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SucursalCombinacion;

class SucursalArticuloController extends Controller
{
    // ✅ Vista principal del stock
    public function vistaStock($id)
    {
        $sucursal = Sucursal::findOrFail($id);
        return view('sucursal.stock', compact('sucursal'));
    }

    // ✅ Listar artículos de una sucursal
    public function index($id)
    {
        $sucursal = Sucursal::findOrFail($id);

        // ✅ Artículos simples (activos e inactivos)
        $articulos = SucursalArticulo::with('articulo')
            ->where('sucursal_id', $id)
            ->get();

        // ✅ Combinaciones (activos e inactivos)
        $combinaciones = SucursalCombinacion::with(['combinacion.producto'])
            ->where('sucursal_id', $id)
            ->get();

        return response()->json([
            'estado'        => 1,
            'sucursal'      => $sucursal,
            'articulos'     => $articulos,
            'combinaciones' => $combinaciones
        ]);
    }


    // ✅ Agregar artículo a la sucursal
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sucursal_id' => 'required|exists:sucursales,id',
            'articulo_id' => 'required|exists:productos,idarticulo',
            'stock' => 'nullable|numeric|min:0',
            'ubicacion' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => $validator->errors()->all()
            ]);
        }

        // ✅ Evitar duplicados
        $existe = SucursalArticulo::where('sucursal_id', $request->sucursal_id)
            ->where('articulo_id', $request->articulo_id)
            ->with('articulo') // 🔹 cargamos relación para obtener nombre/código
            ->first();

        if ($existe) {
            return response()->json([
                'estado' => 0,
                'mensaje' => [
                    "El artículo '{$existe->articulo->nombre}' (Código: {$existe->articulo->codigo}) ya existe en esta sucursal"
                ]
            ]);
        }

        $sa = SucursalArticulo::create([
            'sucursal_id' => $request->sucursal_id,
            'articulo_id' => $request->articulo_id,
            'stock' => $request->stock ?? 0,
            'ubicacion' => $request->ubicacion,
            'activo' => 1
        ]);

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Artículo agregado a la sucursal',
            'registro' => $sa
        ]);
    }

    // ✅ Actualizar stock
    public function updateStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sucursal_articulo,id',
            'stock' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => $validator->errors()->all()
            ]);
        }

        $sa = SucursalArticulo::findOrFail($request->id);
        $sa->stock = $request->stock;
        $sa->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Stock actualizado'
        ]);
    }

    // ✅ Actualizar ubicación interna
    public function updateUbicacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sucursal_articulo,id',
            'ubicacion' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => $validator->errors()->all()
            ]);
        }

        $sa = SucursalArticulo::findOrFail($request->id);
        $sa->ubicacion = $request->ubicacion;
        $sa->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Ubicación actualizada'
        ]);
    }

    // ✅ Baja lógica del artículo en la sucursal
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sucursal_articulo,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => $validator->errors()->all()
            ]);
        }

        $sa = SucursalArticulo::findOrFail($request->id);
        $sa->activo = 0;
        $sa->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Artículo desactivado en esta sucursal'
        ]);
    }

    public function activar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sucursal_articulo,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => $validator->errors()->all()
            ]);
        }

        $sa = SucursalArticulo::findOrFail($request->id);
        $sa->activo = 1;
        $sa->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Artículo reactivado en esta sucursal'
        ]);
    }

}