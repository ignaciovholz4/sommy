<?php

namespace App\Services\Reposicion;

use App\Models\DetallePedidoCompra;
use App\Models\PedidoCompra;
use App\Models\ReposicionAjuste;
use App\Models\SucursalArticulo;
use App\Models\TipoComprobante;
use Illuminate\Support\Facades\DB;

/**
 * Compara velocidad de venta vs. stock/umbral por articulo y sucursal, y
 * genera pedidos de compra BORRADOR agrupados por proveedor. Nunca crea una
 * Compra real ni toca stock: el borrador se revisa y convierte a mano,
 * igual que un pedido de compra armado manualmente.
 */
class SugerenciaReposicionService
{
    public function __construct(protected VelocidadVentaService $velocidad)
    {
    }

    /**
     * @return array{pedidos: array, sin_proveedor: array, analizados: int}
     */
    public function generar(): array
    {
        $ajustes = ReposicionAjuste::actual();

        if (!$ajustes->activo) {
            return ['pedidos' => [], 'sin_proveedor' => [], 'analizados' => 0];
        }

        $porReponer = SucursalArticulo::with('articulo.proveedor', 'articulo.ivaCompra')
            ->where('activo', 1)
            ->get()
            ->filter(fn ($sa) => $sa->articulo !== null)
            ->map(function ($sa) use ($ajustes) {
                $stockMinimo = $sa->stock_minimo ?? $ajustes->stock_minimo_default;
                $velocidadDiaria = $this->velocidad->calcular($sa->sucursal_id, $sa->articulo_id, $ajustes->ventana_analisis_dias);
                $objetivoCobertura = $velocidadDiaria * $ajustes->dias_cobertura_objetivo;
                $nivelObjetivo = max($stockMinimo, $objetivoCobertura);

                return [
                    'sucursal_articulo' => $sa,
                    'stock_minimo' => $stockMinimo,
                    'velocidad_diaria' => $velocidadDiaria,
                    'cantidad_sugerida' => $sa->stock < $stockMinimo ? (int) ceil($nivelObjetivo - $sa->stock) : 0,
                ];
            })
            ->filter(fn ($r) => $r['cantidad_sugerida'] > 0)
            ->values();

        $sinProveedor = $porReponer->filter(fn ($r) => $r['sucursal_articulo']->articulo->proveedor_id === null)->values();
        $conProveedor = $porReponer->filter(fn ($r) => $r['sucursal_articulo']->articulo->proveedor_id !== null)->values();

        $grupos = $conProveedor->groupBy(fn ($r) => $r['sucursal_articulo']->articulo->proveedor_id . '-' . $r['sucursal_articulo']->sucursal_id);

        $pedidosGenerados = [];
        foreach ($grupos as $grupo) {
            $pedidosGenerados[] = $this->crearPedidoBorrador($grupo, $ajustes);
        }

        return [
            'pedidos' => $pedidosGenerados,
            'sin_proveedor' => $sinProveedor->map(fn ($r) => [
                'articulo' => $r['sucursal_articulo']->articulo->nombre,
                'sucursal_id' => $r['sucursal_articulo']->sucursal_id,
                'stock' => $r['sucursal_articulo']->stock,
                'cantidad_sugerida' => $r['cantidad_sugerida'],
            ])->values()->all(),
            'analizados' => $porReponer->count(),
        ];
    }

    protected function crearPedidoBorrador($grupo, ReposicionAjuste $ajustes): array
    {
        $primero = $grupo->first()['sucursal_articulo'];
        $proveedor = $primero->articulo->proveedor;

        return DB::transaction(function () use ($grupo, $proveedor, $primero, $ajustes) {
            $pedido = new PedidoCompra();
            $pedido->user_id = null;
            $pedido->proveedor_id = $proveedor->idproveedor;
            $pedido->sucursal_id = $primero->sucursal_id;
            $pedido->tipo_comprobante_id = optional(TipoComprobante::operativos()->orderBy('idtipo_comprobante')->first())->idtipo_comprobante;
            $pedido->fecha = now()->toDateString();
            $pedido->estado = 'borrador';
            $pedido->origen = 'ia_reposicion';
            $pedido->a_credito = (int) ($proveedor->condicion_pago_dias ?? 0) > 0;
            $pedido->observaciones = 'Generado automaticamente por reposicion inteligente '
                . '(ventana de analisis: ' . $ajustes->ventana_analisis_dias . ' dias, cobertura objetivo: ' . $ajustes->dias_cobertura_objetivo . ' dias). '
                . 'Revisar cantidades y precios antes de convertir en compra.';
            $pedido->total_neto = 0;
            $pedido->total_con_iva = 0;
            $pedido->save();

            $pedido->num_folio = 'PC-' . str_pad($pedido->id, 6, '0', STR_PAD_LEFT);
            $pedido->save();

            $totalNeto = 0;
            $totalConIva = 0;

            foreach ($grupo as $r) {
                $articulo = $r['sucursal_articulo']->articulo;
                $precioBase = (float) ($articulo->pcompra_con_iva ?? 0);
                $descuento = (float) ($articulo->descuento ?? 0);
                $iva = (float) ($articulo->ivaCompra->value_iva ?? 0);

                $precioConDescuento = $precioBase - ($precioBase * $descuento / 100);
                $subtotalNeto = $r['cantidad_sugerida'] * $precioConDescuento;
                $montoIva = $subtotalNeto * ($iva / 100);
                $subtotalConIva = $subtotalNeto + $montoIva;

                DetallePedidoCompra::create([
                    'pedido_compra_id' => $pedido->id,
                    'articulo_id' => $articulo->idarticulo,
                    'tipo_producto_id' => 1,
                    'cantidad' => $r['cantidad_sugerida'],
                    'precio_unitario' => $precioBase,
                    'descuento' => $descuento,
                    'iva' => $iva,
                    'subtotal_neto' => $subtotalNeto,
                    'subtotal_con_iva' => $subtotalConIva,
                ]);

                $totalNeto += $subtotalNeto;
                $totalConIva += $subtotalConIva;
            }

            $pedido->update(['total_neto' => $totalNeto, 'total_con_iva' => $totalConIva]);

            return [
                'pedido_id' => $pedido->id,
                'num_folio' => $pedido->num_folio,
                'proveedor' => $proveedor->nombre,
                'items' => $grupo->count(),
                'total_con_iva' => $totalConIva,
            ];
        });
    }
}
