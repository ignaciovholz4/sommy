<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\PuntoDeVenta;
use App\Models\SucursalArticulo;
use App\Models\SucursalCombinacion;
use App\Models\CajaApertura;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SucursalController extends Controller
{
    // ✅ Vista principal
    public function index()
    {
        return view('sucursal.index');
    }

    public function list()
    {
        $sucursales = Sucursal::withCount(['articulos', 'combinaciones'])->get();

        // Agregar un atributo calculado: total de productos (simples + combinaciones)
        $sucursales->each(function ($s) {
            $s->productos = $s->articulos_count + $s->combinaciones_count;
        });

        return response()->json([
            'estado' => 1,
            'sucursales' => $sucursales
        ]);
    }

    // ✅ Crear sucursal
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required',
            'codigo' => 'nullable|unique:sucursales,codigo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => $validator->errors()->all()
            ]);
        }

        $sucursal = Sucursal::create([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'activo' => 1
        ]);

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Sucursal creada correctamente',
            'sucursal' => $sucursal
        ]);
    }

    // ✅ Ver sucursal + puntos de venta
    public function show($id)
    {
        $sucursal = Sucursal::with('puntosDeVenta')->findOrFail($id);

        return response()->json([
            'estado' => 1,
            'sucursal' => $sucursal
        ]);
    }

    // ✅ Actualizar sucursal (AJUSTADO)
    public function update(Request $request)
    {
        $id = $request->id; // ✅ Recibido desde el JS

        $sucursal = Sucursal::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required',
            'codigo' => 'nullable|unique:sucursales,codigo,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => $validator->errors()->all()
            ]);
        }

        $sucursal->update([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
        ]);

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Sucursal actualizada correctamente'
        ]);
    }

    // ✅ Baja lógica (AJUSTADO)
    public function destroy(Request $request)
    {
        $id = $request->id; // ✅ Recibido desde el JS

        $sucursal = Sucursal::findOrFail($id);
        $sucursal->activo = 0;
        $sucursal->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Sucursal desactivada'
        ]);
    }

    // ✅ Agregar punto de venta a sucursal
    public function addPuntoVenta(Request $request, $sucursalId)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required',
            'codigo' => 'nullable|unique:puntos_venta,codigo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => $validator->errors()->all()
            ]);
        }

        $pv = PuntoDeVenta::create([
            'sucursal_id' => $sucursalId,
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'activo' => 1
        ]);

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Punto de venta agregado',
            'punto_venta' => $pv
        ]);
    }

    // ✅ Eliminar punto de venta
    public function deletePuntoVenta($id)
    {
        $pv = PuntoDeVenta::findOrFail($id);
        $pv->activo = 0;
        $pv->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Punto de venta desactivado'
        ]);
    }

    public function activar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:sucursales,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estado' => 0,
                'mensaje' => $validator->errors()->all()
            ]);
        }

        $sucursal = Sucursal::findOrFail($request->id);
        $sucursal->activo = 1;
        $sucursal->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Sucursal reactivada correctamente'
        ]);
    }

    public function cuentasDisponibles($id)
    {
        $sucursal = Sucursal::findOrFail($id);

        // 🔹 Cajas abiertas (cuentas tipo caja con apertura activa)
        $aperturas = CajaApertura::with('cuenta.moneda')
            ->whereHas('cuenta', fn($q) => $q->where('sucursal_id', $sucursal->id))
            ->where('abierta', true)
            ->whereNull('fecha_cierre')
            ->get();

        $cajas = $aperturas->map(function ($ap) {
            return [
                'id'             => $ap->id,              // id de la apertura
                'cuenta_id'      => $ap->cuenta->id,      // id de la cuenta asociada
                'nombre'         => $ap->cuenta->nombre,
                'tipo'           => 'caja',
                'fecha_apertura' => $ap->fecha_apertura->format('d/m/Y H:i'),
                'moneda'         => $ap->cuenta->moneda->codigo,
            ];
        });

        // 🔹 Bancos (todas las cuentas tipo banco de la sucursal)
        $bancos = Cuenta::with('moneda')
            ->where('sucursal_id', $sucursal->id)
            ->where('tipo', 'banco')
            ->get()
            ->map(function ($banco) {
                return [
                    'id'        => $banco->id,
                    'nombre'    => $banco->nombre,
                    'tipo'      => 'banco',
                    'moneda'    => $banco->moneda->codigo,
                ];
            });

        return response()->json([
            'estado' => 1,
            'cajas'  => $cajas,
            'bancos' => $bancos,
        ]);
    }

    public function articulosDisponibles($id, Request $request)
    {
        $sucursal = Sucursal::findOrFail($id);
        $context = $request->get('context', 'venta'); // por defecto ventas

        $articulosQuery = SucursalArticulo::with(['articulo.ivaVenta', 'articulo.ivaCompra'])
            ->where('sucursal_id', $sucursal->id)
            ->where('activo', 1);

        // 🔹 Sólo filtrar stock en ventas/presupuesto
        if (in_array($context, ['venta', 'presupuesto'])) {
            $articulosQuery->where('stock', '>', 0);
        }

        // En compras se usa el nombre del proveedor (nombre_compra); en ventas el comercial
        $esCompra = $context === 'compra';

        $articulos = $articulosQuery->get()->map(function ($sa) use ($esCompra) {
            return [
                'idarticulo'       => $sa->articulo->idarticulo,
                'producto_id'      => $sa->articulo->idarticulo,
                'tipo_producto_id' => $sa->articulo->tipo_producto_id,
                'nombre'           => $esCompra ? ($sa->articulo->nombre_compra ?: $sa->articulo->nombre) : $sa->articulo->nombre,
                'codigo'           => $sa->articulo->codigo,
                'pventa_con_iva'   => $sa->articulo->pventa_con_iva,
                'pcompra_con_iva'  => $sa->articulo->pcompra_con_iva,
                'iva_venta'        => $sa->articulo->ivaVenta->value_iva ?? 0,
                'iva_compra'       => $sa->articulo->ivaCompra->value_iva ?? 0,
                'stock'            => $sa->stock,
                'ubicacion'        => $sa->ubicacion,
                'descuento'        => $sa->articulo->descuento ?? 0,
            ];
        });

        $combinacionesQuery = SucursalCombinacion::with(['combinacion.producto.ivaVenta', 'combinacion.producto.ivaCompra'])
            ->where('sucursal_id', $sucursal->id)
            ->where('activo', 1);

        if (in_array($context, ['venta', 'presupuesto'])) {
            $combinacionesQuery->where('stock', '>', 0);
        }

        $combinaciones = $combinacionesQuery->get()->map(function ($sc) use ($esCompra) {
            return [
                'idcombinacion'    => $sc->combinacion->idcombinacion,
                'producto_id'      => $sc->combinacion->producto->idarticulo,
                'tipo_producto_id' => 2,
                'nombre'           => ($esCompra ? ($sc->combinacion->producto->nombre_compra ?: $sc->combinacion->producto->nombre) : $sc->combinacion->producto->nombre) . ' - ' . $sc->combinacion->combinacion,
                'codigo'           => $sc->combinacion->sku ?: $sc->combinacion->producto->codigo,
                'sku'              => $sc->combinacion->sku,
                'pventa_con_iva'   => $sc->combinacion->pventa_variante,
                'pcompra_con_iva'  => $sc->combinacion->pcompra_variante,
                'iva_venta'        => $sc->combinacion->producto->ivaVenta->value_iva ?? 0,
                'iva_compra'       => $sc->combinacion->producto->ivaCompra->value_iva ?? 0,
                'stock'            => $sc->stock,
                'ubicacion'        => $sc->ubicacion,
                'descuento'        => 0,
            ];
        });

        return response()->json([
            'estado'        => 1,
            'sucursal'      => $sucursal->nombre,
            'articulos'     => $articulos,
            'combinaciones' => $combinaciones
        ]);
    }

    public function cuentasDisponiblesGlobal()
    {
        // 🔹 Cajas abiertas en todas las sucursales
        $aperturas = CajaApertura::with(['cuenta.moneda','cuenta.sucursal'])
            ->where('abierta', true)
            ->whereNull('fecha_cierre')
            ->get();

        $cajas = $aperturas->map(function ($ap) {
            return [
                'id'             => $ap->id,              // id de la apertura
                'cuenta_id'      => $ap->cuenta->id,      // id de la cuenta asociada
                'sucursal'       => $ap->cuenta->sucursal->nombre,
                'nombre'         => $ap->cuenta->nombre,
                'tipo'           => 'caja',
                'fecha_apertura' => $ap->fecha_apertura->format('d/m/Y H:i'),
                'moneda'         => $ap->cuenta->moneda->codigo,
            ];
        });

        // 🔹 Bancos de todas las sucursales
        $bancos = Cuenta::with(['moneda','sucursal'])
            ->where('tipo', 'banco')
            ->get()
            ->map(function ($banco) {
                return [
                    'id'        => $banco->id,
                    'sucursal'  => $banco->sucursal->nombre,
                    'nombre'    => $banco->nombre,
                    'tipo'      => 'banco',
                    'moneda'    => $banco->moneda->codigo,
                ];
            });

        return response()->json([
            'estado' => 1,
            'cajas'  => $cajas,
            'bancos' => $bancos,
        ]);
    }

}