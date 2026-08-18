<?php

namespace App\Http\Controllers;

use App\Models\SucursalArticulo;
use App\Models\MovimientoStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MovimientoStockController extends Controller
{
    // ✅ Registrar entrada de stock
    public function entrada(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sucursal_id' => 'required|exists:sucursales,id',
            'articulo_id' => 'required|exists:productos,idarticulo',
            'cantidad' => 'required|numeric|min:1',
            'observacion' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => 0, 'mensaje' => $validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {
            // ✅ Buscar registro en sucursal_articulo
            $sa = SucursalArticulo::firstOrCreate(
                [
                    'sucursal_id' => $request->sucursal_id,
                    'articulo_id' => $request->articulo_id
                ],
                ['stock' => 0]
            );

            // ✅ Actualizar stock
            $sa->stock += $request->cantidad;
            $sa->save();

            // ✅ Registrar movimiento
            MovimientoStock::create([
                'sucursal_destino_id' => $request->sucursal_id,
                'articulo_id' => $request->articulo_id,
                'cantidad' => $request->cantidad,
                'tipo' => 'entrada',
                'observacion' => $request->observacion
            ]);

            DB::commit();

            return response()->json(['estado' => 1, 'mensaje' => 'Entrada registrada correctamente']);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['estado' => 0, 'mensaje' => $th->getMessage()]);
        }
    }

    // ✅ Registrar salida de stock
    public function salida(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sucursal_id' => 'required|exists:sucursales,id',
            'articulo_id' => 'required|exists:productos,idarticulo',
            'cantidad' => 'required|numeric|min:1',
            'observacion' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => 0, 'mensaje' => $validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {
            $sa = SucursalArticulo::where('sucursal_id', $request->sucursal_id)
                ->where('articulo_id', $request->articulo_id)
                ->firstOrFail();

            if ($sa->stock < $request->cantidad) {
                return response()->json(['estado' => 0, 'mensaje' => ['Stock insuficiente']]);
            }

            $sa->stock -= $request->cantidad;
            $sa->save();

            MovimientoStock::create([
                'sucursal_origen_id' => $request->sucursal_id,
                'articulo_id' => $request->articulo_id,
                'cantidad' => $request->cantidad,
                'tipo' => 'salida',
                'observacion' => $request->observacion
            ]);

            DB::commit();

            return response()->json(['estado' => 1, 'mensaje' => 'Salida registrada correctamente']);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['estado' => 0, 'mensaje' => $th->getMessage()]);
        }
    }

    // ✅ Transferencia entre sucursales
    public function transferencia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sucursal_origen_id' => 'required|exists:sucursales,id',
            'sucursal_destino_id' => 'required|exists:sucursales,id|different:sucursal_origen_id',
            'articulo_id' => 'required|exists:productos,idarticulo',
            'cantidad' => 'required|numeric|min:1',
            'observacion' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => 0, 'mensaje' => $validator->errors()->all()]);
        }

        DB::beginTransaction();

        try {
            // ✅ Origen
            $origen = SucursalArticulo::where('sucursal_id', $request->sucursal_origen_id)
                ->where('articulo_id', $request->articulo_id)
                ->firstOrFail();

            if ($origen->stock < $request->cantidad) {
                return response()->json(['estado' => 0, 'mensaje' => ['Stock insuficiente en sucursal origen']]);
            }

            $origen->stock -= $request->cantidad;
            $origen->save();

            // ✅ Destino
            $destino = SucursalArticulo::firstOrCreate(
                [
                    'sucursal_id' => $request->sucursal_destino_id,
                    'articulo_id' => $request->articulo_id
                ],
                ['stock' => 0]
            );

            $destino->stock += $request->cantidad;
            $destino->save();

            // ✅ Registrar movimiento
            MovimientoStock::create([
                'sucursal_origen_id' => $request->sucursal_origen_id,
                'sucursal_destino_id' => $request->sucursal_destino_id,
                'articulo_id' => $request->articulo_id,
                'cantidad' => $request->cantidad,
                'tipo' => 'transferencia',
                'observacion' => $request->observacion
            ]);

            DB::commit();

            return response()->json(['estado' => 1, 'mensaje' => 'Transferencia realizada correctamente']);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['estado' => 0, 'mensaje' => $th->getMessage()]);
        }
    }
}