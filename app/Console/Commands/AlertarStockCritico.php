<?php

namespace App\Console\Commands;

use App\Models\Notificacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Revisa el stock y deja una notificación con los productos sin stock
 * y los que están por agotarse (≤ 5 unidades). Corre a diario.
 *
 * Para no repetir la misma alerta todos los días mientras la situación no
 * cambia, se recuerda (en cache) el set de productos avisado la última vez:
 * solo se notifica de nuevo si ese set cambió (se sumó/saco un producto) o si
 * se habia resuelto (stock repuesto) y vuelve a caer.
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

        $this->avisarSiCambio('stock_sin_stock', $sinStock, fn ($items) => Notificacion::avisar('stock',
            '⛔ ' . $items->count() . ' producto(s) SIN stock',
            $items->take(4)->pluck('nombre')->implode(', ') . ($items->count() > 4 ? '…' : ''),
            url('almacen/articulo'), 'alerta'));

        $this->avisarSiCambio('stock_bajo', $bajo, fn ($items) => Notificacion::avisar('stock',
            $items->count() . ' producto(s) con stock bajo (≤ 5 u.)',
            $items->take(4)->map(fn ($p) => $p->nombre . ' (' . (int) $p->stock . ')')->implode(', ') . ($items->count() > 4 ? '…' : ''),
            url('almacen/articulo'), 'alerta'));

        $this->info('Sin stock: ' . $sinStock->count() . ' · Stock bajo: ' . $bajo->count());

        return self::SUCCESS;
    }

    /**
     * Notifica solo si el set de productos afectados cambio desde la ultima
     * vez (nuevo producto entro en la lista, o la lista se habia vaciado y
     * volvio a aparecer). Si es exactamente el mismo set que ya se avisó, no
     * repite la notificación.
     */
    protected function avisarSiCambio(string $clave, $items, callable $notificar): void
    {
        $ids = $items->pluck('idarticulo')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $cacheKey = "alerta_{$clave}_ids";

        if (empty($ids)) {
            Cache::forget($cacheKey); // se resolvio: la proxima vez que aparezca, vuelve a avisar
            return;
        }

        if (Cache::get($cacheKey) === $ids) {
            return; // misma situacion que la ultima vez avisada, no repetir
        }

        $notificar($items);
        Cache::forever($cacheKey, $ids);
    }
}
