<?php

namespace App\Http\Controllers;
use App\Models\SucursalArticulo; 
use App\Models\SucursalCombinacion; 
use App\Models\Articulo;
use App\Models\ecommerce\order_ecommerce;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function productosConStock()
    {
        $productos = $this->getProductosConStock();

        return response()->json([
            'estado'    => 1,
            'productos' => $productos
        ]);
    }

    public function getProductosConStock()
    {
        // ✅ Stock de artículos simples
        $stockSimples = SucursalArticulo::with('articulo')
            ->selectRaw('articulo_id, SUM(stock) as total_stock')
            ->where('activo', 1)
            ->groupBy('articulo_id')
            ->havingRaw('SUM(stock) > 0')
            ->get();

        // ✅ Stock de combinaciones (agrupado por producto principal)
        $stockCombinaciones = SucursalCombinacion::with('combinacion.producto')
            ->selectRaw('combinacion_id, SUM(stock) as total_stock')
            ->where('activo', 1)
            ->groupBy('combinacion_id')
            ->havingRaw('SUM(stock) > 0')
            ->get()
            ->groupBy(fn($sc) => $sc->combinacion->producto_id) // agrupar por producto principal
            ->map(function ($items, $productoId) {
                return [
                    'producto_id' => $productoId,
                    'total_stock' => $items->sum('total_stock'),
                    'producto'    => $items->first()->combinacion->producto
                ];
            })
            ->values();

        // ✅ Unir ambos resultados
        $productos = collect();

        foreach ($stockSimples as $sa) {
            $productos->push([
                'producto_id' => $sa->articulo_id,
                'total_stock' => $sa->total_stock,
                'producto'    => $sa->articulo
            ]);
        }

        foreach ($stockCombinaciones as $sc) {
            $productos->push($sc);
        }

        // ✅ Evitar duplicados (si un producto existe como simple y también como combinaciones)
        $productos = $productos->groupBy('producto_id')->map(function ($items) {
            $producto = $items->first()['producto'];
            $total = collect($items)->sum('total_stock');
            
            $obj = new \stdClass();
            $obj->producto_id = $producto->idarticulo;
            $obj->producto    = $producto;
            $obj->total_stock = $total;

            return $obj;
        })->values();

        return $productos;
    }

    public function getProductoConStockPorId(int $productoId)
    {
        // ✅ Buscar artículo simple con stock
        $stockSimple = SucursalArticulo::with('articulo')
            ->selectRaw('articulo_id, SUM(stock) as total_stock')
            ->where('activo', 1)
            ->where('articulo_id', $productoId)
            ->groupBy('articulo_id')
            ->havingRaw('SUM(stock) > 0')
            ->first();

        // ✅ Buscar combinaciones de ese producto
        $stockCombinaciones = SucursalCombinacion::with('combinacion.producto')
            ->selectRaw('combinacion_id, SUM(stock) as total_stock')
            ->where('activo', 1)
            ->whereHas('combinacion', function ($q) use ($productoId) {
                $q->where('producto_id', $productoId);
            })
            ->groupBy('combinacion_id')
            ->havingRaw('SUM(stock) > 0')
            ->get();

        // ✅ Armar respuesta
        $producto = null;
        $totalStock = 0;

        if ($stockSimple) {
            $producto = $stockSimple->articulo;
            $totalStock += $stockSimple->total_stock;
        }

        if ($stockCombinaciones->isNotEmpty()) {
            $producto = $stockCombinaciones->first()->combinacion->producto;
            $totalStock += $stockCombinaciones->sum('total_stock');
        }

        if (!$producto) {
            return null; // No hay stock ni activo
        }

        return (object)[
            'producto_id'   => $producto->idarticulo,
            'producto'      => $producto,
            'total_stock'   => $totalStock,
            'combinaciones' => $stockCombinaciones->map(function ($sc) {
                return (object)[
                    'combinacion_id' => $sc->combinacion_id,
                    'total_stock'    => $sc->total_stock,
                    'combinacion'    => $sc->combinacion
                ];
            })
        ];
    }

    public function getProductoConStockPorSucursal(int $productoId, int $sucursalId)
    {
        // ✅ Stock simple del artículo en la sucursal
        $stockSimple = SucursalArticulo::with('articulo')
            ->selectRaw('articulo_id, SUM(stock) as total_stock')
            ->where('activo', 1)
            ->where('articulo_id', $productoId)
            ->where('sucursal_id', $sucursalId) // ✅ filtro por sucursal
            ->groupBy('articulo_id')
            ->first();

        // ✅ Stock de combinaciones en la sucursal
        $stockCombinaciones = SucursalCombinacion::with('combinacion.producto')
            ->selectRaw('combinacion_id, SUM(stock) as total_stock')
            ->where('activo', 1)
            ->where('sucursal_id', $sucursalId) // ✅ filtro por sucursal
            ->whereHas('combinacion', function ($q) use ($productoId) {
                $q->where('producto_id', $productoId);
            })
            ->groupBy('combinacion_id')
            ->get();

        $producto = null;
        $totalStock = 0;

        if ($stockSimple) {
            $producto = $stockSimple->articulo;
            $totalStock += $stockSimple->total_stock;
        }

        if ($stockCombinaciones->isNotEmpty()) {
            $producto = $stockCombinaciones->first()->combinacion->producto;
            $totalStock += $stockCombinaciones->sum('total_stock');
        }

        if (!$producto) {
            return null; // No hay stock en esa sucursal
        }

        return (object)[
            'producto_id'   => $producto->idarticulo,
            'producto'      => $producto,
            'total_stock'   => $totalStock,
            'combinaciones' => $stockCombinaciones->map(function ($sc) {
                return (object)[
                    'combinacion_id' => $sc->combinacion_id,
                    'total_stock'    => $sc->total_stock,
                    'combinacion'    => $sc->combinacion
                ];
            })
        ];
    }

    public function disminuirStockEnSucursal(int $sucursalId, int $articuloId, int $cantidad, ?int $combinacionId = null): void
    {
        if ($combinacionId) {
            // ✅ Descontar stock de la combinación específica
            $stockCombinacion = SucursalCombinacion::where('sucursal_id', $sucursalId)
                ->where('combinacion_id', $combinacionId)
                ->where('activo', 1)
                ->sum('stock');

            if ($stockCombinacion < $cantidad) {
                throw new \Exception("Stock insuficiente en sucursal para la combinación ID {$combinacionId}. Disponible: {$stockCombinacion}");
            }

            SucursalCombinacion::where('sucursal_id', $sucursalId)
                ->where('combinacion_id', $combinacionId)
                ->decrement('stock', $cantidad);

        } else {
            // ✅ Descontar stock del artículo simple
            $stockArticulo = SucursalArticulo::where('sucursal_id', $sucursalId)
                ->where('articulo_id', $articuloId)
                ->where('activo', 1)
                ->sum('stock');

            if ($stockArticulo < $cantidad) {
                throw new \Exception("Stock insuficiente en sucursal para el artículo ID {$articuloId}. Disponible: {$stockArticulo}");
            }

            SucursalArticulo::where('sucursal_id', $sucursalId)
                ->where('articulo_id', $articuloId)
                ->decrement('stock', $cantidad);
        }
    }

    public function incrementarStockEnSucursal(int $sucursalId, int $articuloId, int $cantidad, ?int $combinacionId = null): void
    {
        if ($combinacionId) {
            SucursalCombinacion::where('sucursal_id', $sucursalId)
                ->where('combinacion_id', $combinacionId)
                ->increment('stock', $cantidad);
        } else {
            SucursalArticulo::where('sucursal_id', $sucursalId)
                ->where('articulo_id', $articuloId)
                ->increment('stock', $cantidad);
        }
    }

    public function transferirStock(Request $request)
    {
        $request->validate([
            'origen_id'   => 'required|integer',
            'destino_id'  => 'required|integer|different:origen_id',
            'producto_id' => 'required|integer',
            'cantidad'    => 'required|integer|min:1',
            'tipo'        => 'required|string|in:simple,combinacion',
        ]);

        DB::beginTransaction();
        try {
            $origenId   = $request->origen_id;
            $destinoId  = $request->destino_id;
            $productoId = $request->producto_id;
            $cantidad   = $request->cantidad;
            $tipo       = $request->tipo;

            if ($tipo === 'combinacion') {
                // 🔹 Disminuir stock en origen
                $this->disminuirStockEnSucursal($origenId, 0, $cantidad, $productoId);

                // 🔹 Verificar si destino ya tiene la combinación
                $existe = SucursalCombinacion::where('sucursal_id', $destinoId)
                    ->where('combinacion_id', $productoId)
                    ->exists();

                if (!$existe) {
                    SucursalCombinacion::create([
                        'sucursal_id'   => $destinoId,
                        'combinacion_id'=> $productoId,
                        'stock'         => 0,
                        'activo'        => 1,
                    ]);
                }

                // 🔹 Incrementar stock en destino
                $this->incrementarStockEnSucursal($destinoId, 0, $cantidad, $productoId);

            } else {
                // 🔹 Disminuir stock en origen
                $this->disminuirStockEnSucursal($origenId, $productoId, $cantidad);

                // 🔹 Verificar si destino ya tiene el artículo
                $existe = SucursalArticulo::where('sucursal_id', $destinoId)
                    ->where('articulo_id', $productoId)
                    ->exists();

                if (!$existe) {
                    SucursalArticulo::create([
                        'sucursal_id' => $destinoId,
                        'articulo_id' => $productoId,
                        'stock'       => 0,
                        'activo'      => 1,
                    ]);
                }

                // 🔹 Incrementar stock en destino
                $this->incrementarStockEnSucursal($destinoId, $productoId, $cantidad);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Stock transferido correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getSucursalesConStockProducto(int $productoId)
    {
        $stockSimple = SucursalArticulo::with(['articulo','sucursal'])
            ->where('activo', 1)
            ->where('articulo_id', $productoId)
            ->selectRaw('sucursal_id, articulo_id, SUM(stock) as total_stock')
            ->groupBy('sucursal_id','articulo_id')
            ->havingRaw('SUM(stock) > 0')
            ->get();

        $stockCombinaciones = SucursalCombinacion::with(['combinacion.producto','sucursal'])
            ->where('activo', 1)
            ->whereHas('combinacion', function ($q) use ($productoId) {
                $q->where('producto_id', $productoId);
            })
            ->selectRaw('sucursal_id, combinacion_id, SUM(stock) as total_stock')
            ->groupBy('sucursal_id','combinacion_id')
            ->havingRaw('SUM(stock) > 0')
            ->get();

        $resultado = [];

        foreach ($stockSimple as $ss) {
            $resultado[$ss->sucursal_id]['sucursal_id'] = $ss->sucursal_id;
            $resultado[$ss->sucursal_id]['sucursal'] = $ss->sucursal->nombre;
            $resultado[$ss->sucursal_id]['articulo'] = $ss->articulo->nombre;
            $resultado[$ss->sucursal_id]['stock_simple'] = (int)$ss->total_stock;
            $resultado[$ss->sucursal_id]['combinaciones'] = [];
        }

        foreach ($stockCombinaciones as $sc) {
            $resultado[$sc->sucursal_id]['sucursal_id'] = $sc->sucursal_id;
            $resultado[$sc->sucursal_id]['sucursal'] = $sc->sucursal->nombre;
            $resultado[$sc->sucursal_id]['articulo'] = $sc->combinacion->producto->nombre;
            $resultado[$sc->sucursal_id]['stock_simple'] = $resultado[$sc->sucursal_id]['stock_simple'] ?? 0;
            $resultado[$sc->sucursal_id]['combinaciones'][] = [
                'combinacion_id' => $sc->combinacion_id,
                'nombre'         => $sc->combinacion->combinacion,
                'stock'          => (int)$sc->total_stock,
            ];
        }

        // 🔹 Devolvemos solo el array
        return array_values($resultado);
    }

    public function getProductosOrdenConSucursales(int $orderId)
    {
        // 🔹 Traer productos de la orden con Eloquent
        $order = order_ecommerce::with('detalles.producto')->find($orderId);

        if (!$order) {
            return response()->json([
                'estado' => 0,
                'mensaje' => 'No se encontró la orden'
            ]);
        }

        $resultado = [];

        foreach ($order->detalles as $detalle) {
            $sucursales = $this->getSucursalesConStockProducto($detalle->product_id);

            $resultado[] = [
                'product_id'   => $detalle->product_id,
                'name_product' => $detalle->producto->nombre,
                'quantity'     => $detalle->quantity,
                'sucursales'   => $sucursales
            ];
        }

        return response()->json([
            'estado'    => 1,
            'orderId'   => $orderId,
            'productos' => $resultado
        ]);
    }

}
