<?php

namespace App\Services\Ai\ReportesTools;

use Illuminate\Support\Facades\DB;

/** Foto actual de stock: totales, valorizado, stock critico y por categoria. */
class StockQueryTool
{
    public static function definition(): array
    {
        return [
            'name' => 'consultar_stock',
            'description' => 'Foto actual de stock (no historica): unidades y valor total, artículos con stock crítico, y stock por categoría. Opcionalmente filtrado por sucursal.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'sucursal_id' => ['type' => 'integer', 'description' => 'Filtrar por una sucursal (opcional)'],
                    'umbral_critico' => ['type' => 'integer', 'description' => 'Debajo de cuantas unidades se considera stock critico (default 3, maximo 100)'],
                ],
                'required' => [],
            ],
        ];
    }

    public function execute(array $args): array
    {
        $umbral = QueryLimites::limite($args['umbral_critico'] ?? 3, 100);

        $base = fn () => DB::table('sucursal_articulo as sa')
            ->join('productos as p', 'p.idarticulo', '=', 'sa.articulo_id')
            ->where('sa.activo', 1)
            ->when(!empty($args['sucursal_id']), fn ($q) => $q->where('sa.sucursal_id', (int) $args['sucursal_id']));

        $totales = $base()
            ->selectRaw('COALESCE(SUM(sa.stock),0) as unidades, COALESCE(SUM(sa.stock * p.pcompra_con_iva),0) as valor_costo, COALESCE(SUM(sa.stock * p.pventa_con_iva),0) as valor_venta')
            ->first();

        $critico = $base()
            ->where('p.estado', 'Activo')
            ->groupBy('p.idarticulo', 'p.nombre')
            ->havingRaw('SUM(sa.stock) <= ?', [$umbral])
            ->selectRaw('p.nombre, SUM(sa.stock) as stock')
            ->orderBy('stock')->limit(30)->get();

        $porCategoria = $base()
            ->join('categorias as c', 'c.idcategoria', '=', 'p.categoria_id')
            ->groupBy('c.idcategoria', 'c.nombre')
            ->selectRaw('c.nombre, COALESCE(SUM(sa.stock),0) as unidades, COALESCE(SUM(sa.stock * p.pventa_con_iva),0) as valor')
            ->orderByDesc('valor')->get();

        return [
            'unidades_totales' => (int) $totales->unidades,
            'valor_costo' => round((float) $totales->valor_costo, 2),
            'valor_venta' => round((float) $totales->valor_venta, 2),
            'umbral_critico_usado' => $umbral,
            'stock_critico' => $critico->map(fn ($r) => ['producto' => $r->nombre, 'stock' => (int) $r->stock])->all(),
            'por_categoria' => $porCategoria->map(fn ($r) => [
                'categoria' => $r->nombre,
                'unidades' => (int) $r->unidades,
                'valor' => round((float) $r->valor, 2),
            ])->all(),
        ];
    }
}
