<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;
use Illuminate\Support\Facades\DB;

class ConsultarStock
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_stock',
            'description' => 'Consulta el stock real disponible de un producto por sucursal. Usá el producto_id que devuelve buscar_productos.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'producto_id' => ['type' => 'integer', 'description' => 'ID del producto'],
                ],
                'required' => ['producto_id'],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $productoId = (int) ($args['producto_id'] ?? 0);
        $producto = DB::table('productos')->where('idarticulo', $productoId)->first();
        if (!$producto) {
            return ['error' => 'Producto inexistente'];
        }

        $query = DB::table('sucursal_articulo as sa')
            ->join('sucursales as s', 's.id', '=', 'sa.sucursal_id')
            ->where('sa.articulo_id', $productoId)
            ->where('sa.activo', 1);

        if ($agent->sucursal_id) {
            $query->where('sa.sucursal_id', $agent->sucursal_id);
        }

        $porSucursal = $query->get(['s.nombre', 'sa.stock']);

        return [
            'producto' => $producto->nombre,
            'stock_total' => (int) $porSucursal->sum('stock'),
            'por_sucursal' => $porSucursal->map(fn ($r) => ['sucursal' => $r->nombre, 'stock' => (int) $r->stock])->all(),
        ];
    }
}
