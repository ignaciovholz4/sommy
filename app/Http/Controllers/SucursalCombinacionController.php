<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\SucursalCombinacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SucursalCombinacionController extends Controller
{
    // ✅ Agregar combinación a la sucursal
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sucursal_id' => 'required|exists:sucursales,id',
            'combinacion_id' => 'required|exists:producto_combinaciones,idcombinacion',
            'stock' => 'nullable|numeric|min:0',
            'ubicacion' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => 0, 'mensaje' => $validator->errors()->all()]);
        }

        $existe = SucursalCombinacion::where('sucursal_id', $request->sucursal_id)
            ->where('combinacion_id', $request->combinacion_id)
            ->with('combinacion.producto')
            ->first();

        if ($existe) {
            $producto = $existe->combinacion->producto;
            $nombre   = $producto->nombre;
            $codigo   = $producto->codigo;
            $atributos = $existe->combinacion->combinacion;

            return response()->json([
                'estado' => 0,
                'mensaje' => [
                    "La combinación '{$nombre} - {$atributos}' (Código: {$codigo}) ya existe en esta sucursal"
                ]
            ]);
        }

        $sc = SucursalCombinacion::create([
            'sucursal_id' => $request->sucursal_id,
            'combinacion_id' => $request->combinacion_id,
            'stock' => $request->stock ?? 0,
            'ubicacion' => $request->ubicacion,
            'activo' => 1
        ]);

        return response()->json(['estado' => 1, 'mensaje' => 'Combinación agregada a la sucursal', 'registro' => $sc]);
    }

    // ✅ Actualizar stock
    public function updateStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sucursal_combinacion,id',
            'stock' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => 0, 'mensaje' => $validator->errors()->all()]);
        }

        $sc = SucursalCombinacion::findOrFail($request->id);
        $sc->stock = $request->stock;
        $sc->save();

        return response()->json(['estado' => 1, 'mensaje' => 'Stock actualizado']);
    }

    // ✅ Actualizar ubicación
    public function updateUbicacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sucursal_combinacion,id',
            'ubicacion' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => 0, 'mensaje' => $validator->errors()->all()]);
        }

        $sc = SucursalCombinacion::findOrFail($request->id);
        $sc->ubicacion = $request->ubicacion;
        $sc->save();

        return response()->json(['estado' => 1, 'mensaje' => 'Ubicación actualizada']);
    }

    // ✅ Baja lógica
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sucursal_combinacion,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => 0, 'mensaje' => $validator->errors()->all()]);
        }

        $sc = SucursalCombinacion::findOrFail($request->id);
        $sc->activo = 0;
        $sc->save();

        return response()->json(['estado' => 1, 'mensaje' => 'Combinación desactivada en esta sucursal']);
    }

    // ✅ Reactivar
    public function activar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sucursal_combinacion,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => 0, 'mensaje' => $validator->errors()->all()]);
        }

        $sc = SucursalCombinacion::findOrFail($request->id);
        $sc->activo = 1;
        $sc->save();

        return response()->json(['estado' => 1, 'mensaje' => 'Combinación reactivada en esta sucursal']);
    }
}