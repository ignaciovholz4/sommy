<?php

namespace App\Console\Commands;

use App\Models\Notificacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Revisa el stock y deja una notificación con los productos sin stock
 * y los que están por agotarse (≤ 5 unidades). Corre a diario.
 */
class AlertarStockCritico extends Command
{
    protected $signature = 'stock:alertar-critico';

    protected $description = 'Notifica productos sin stock o con stock bajo';

    public function handle(): int
    {
        $porProducto = DB::table('productos as p')
            ->leftJoin('sucursal_articulo as sa', function ($j) {
                $j->on('sa.articulo_id', '=', 'p.idarticulo')->where('sa.activo', 1);
            })
            ->where('p.estado', 'Activo')
            ->groupBy('p.idarticulo', 'p.nombre')
            ->selectRaw('p.idarticulo, p.nombre, COALESCE(SUM(sa.stock), 0) as stock')
            ->get();

        $sinStock = $porProducto->where('stock', '<=', 0);
        $bajo = $porProducto->filter(fn ($p) => $p->stock > 0 && $p->stock <= 5);

        if ($sinStock->isNotEmpty()) {
            Notificacion::avisar('stock',
                '⛔ ' . $sinStock->count() . ' producto(s) SIN stock',
                $sinStock->take(4)->pluck('nombre')->implode(', ') . ($sinStock->count() > 4 ? '…' : ''),
                url('almacen/articulos'), 'alerta');
        }

        if ($bajo->isNotEmpty()) {
            Notificacion::avisar('stock',
                $bajo->count() . ' producto(s) con stock bajo (≤ 5 u.)',
                $bajo->take(4)->map(fn ($p) => $p->nombre . ' (' . (int) $p->stock . ')')->implode(', ') . ($bajo->count() > 4 ? '…' : ''),
                url('almacen/articulos'), 'alerta');
        }

        $this->info('Sin stock: ' . $sinStock->count() . ' · Stock bajo: ' . $bajo->count());

        return self::SUCCESS;
    }
}
