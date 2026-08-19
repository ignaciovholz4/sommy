<?php

namespace App\Services\Reposicion;

use Illuminate\Support\Facades\DB;

/**
 * Velocidad de venta de un articulo en una sucursal: unidades por dia
 * vendidas en una ventana de tiempo hacia atras. Mismo join que usa
 * GraphicsController para los productos mas vendidos.
 */
class VelocidadVentaService
{
    public function calcular(int $sucursalId, int $articuloId, int $ventanaDias): float
    {
        $unidades = (float) DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.idventa', '=', 'dv.venta_id')
            ->where('v.estado', 'NOT LIKE', 'Cancel%')
            ->where('v.sucursal_id', $sucursalId)
            ->where('dv.articulo_id', $articuloId)
            ->where('v.fecha', '>=', now()->subDays($ventanaDias))
            ->sum('dv.cantidad');

        return $ventanaDias > 0 ? $unidades / $ventanaDias : 0.0;
    }
}
