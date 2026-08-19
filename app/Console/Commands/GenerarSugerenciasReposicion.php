<?php

namespace App\Console\Commands;

use App\Services\Reposicion\SugerenciaReposicionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Genera pedidos de compra BORRADOR para los articulos que, segun su
 * velocidad de venta reciente, van a quedar por debajo del stock minimo.
 * No toca stock ni compras reales: un humano revisa y convierte cada pedido.
 */
class GenerarSugerenciasReposicion extends Command
{
    protected $signature = 'reposicion:generar-sugerencias';

    protected $description = 'Genera pedidos de compra borrador por reposicion inteligente de stock';

    public function handle(SugerenciaReposicionService $service): int
    {
        $resultado = $service->generar();

        $this->info("Articulos analizados por debajo del minimo: {$resultado['analizados']}");
        $this->info('Pedidos de compra borrador generados: ' . count($resultado['pedidos']));

        foreach ($resultado['pedidos'] as $p) {
            $this->line("- {$p['num_folio']} | {$p['proveedor']} | {$p['items']} articulos | \${$p['total_con_iva']}");
        }

        if (!empty($resultado['sin_proveedor'])) {
            $this->warn(count($resultado['sin_proveedor']) . ' articulo(s) necesitan reposicion pero no tienen proveedor asignado (quedaron sin pedido):');
            foreach ($resultado['sin_proveedor'] as $s) {
                $this->line("- {$s['articulo']} (sucursal {$s['sucursal_id']}, stock {$s['stock']}, sugerido {$s['cantidad_sugerida']})");
            }
        }

        Log::info('Reposicion inteligente: ' . count($resultado['pedidos']) . ' pedidos generados, '
            . count($resultado['sin_proveedor']) . ' articulos sin proveedor asignado.');

        return self::SUCCESS;
    }
}
