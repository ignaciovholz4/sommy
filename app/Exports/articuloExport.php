<?php

namespace App\Exports;

use App\Models\Articulo;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class articuloExport implements FromCollection, WithHeadings, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Stock total por producto: simples (sucursal_articulo) + variantes (sucursal_combinacion)
        // — mismo cálculo que usa la grilla de /almacen/articulo (ArticuloController::show).
        $stockSimples = DB::table('sucursal_articulo')
            ->where('activo', 1)
            ->selectRaw('articulo_id, SUM(stock) as stock')
            ->groupBy('articulo_id');

        $stockVariantes = DB::table('producto_combinaciones as pc')
            ->join('sucursal_combinacion as scb', 'scb.combinacion_id', '=', 'pc.idcombinacion')
            ->where('scb.activo', 1)
            ->selectRaw('pc.producto_id, SUM(scb.stock) as stock')
            ->groupBy('pc.producto_id');

        $stockPorArticulo = DB::table('productos as p')
            ->leftJoinSub($stockSimples, 'ss', 'ss.articulo_id', '=', 'p.idarticulo')
            ->leftJoinSub($stockVariantes, 'sv', 'sv.producto_id', '=', 'p.idarticulo')
            ->selectRaw('p.idarticulo, COALESCE(ss.stock, 0) + COALESCE(sv.stock, 0) as stock_total')
            ->pluck('stock_total', 'idarticulo');

        return Articulo::select(
                'idarticulo',
                'codigo',
                'nombre',
                'descripcion',
                'pcompra_sin_iva',
                'pcompra_con_iva',
                'pventa_sin_iva',
                'pventa_con_iva',
                'descuento',
                'tipo_producto_id',
                'marca_id',
                'articulo_pesable_balanza',
                'iva_compra_id',
                'iva_venta_id',
                'estado'
            )
            ->where('estado', '=', 'Activo')
            ->get()
            ->map(function ($articulo) use ($stockPorArticulo) {
                $margenPct = $articulo->pcompra_sin_iva > 0
                    ? round((($articulo->pventa_sin_iva - $articulo->pcompra_sin_iva) / $articulo->pcompra_sin_iva) * 100)
                    : null;

                return [
                    'Codigo' => $articulo->codigo,
                    'Nombre' => $articulo->nombre,
                    'Descripcion' => $articulo->descripcion,
                    'Stock' => (float) ($stockPorArticulo[$articulo->idarticulo] ?? 0),
                    'Precio compra sin IVA' => $articulo->pcompra_sin_iva,
                    'Precio compra con IVA' => $articulo->pcompra_con_iva,
                    'Precio venta sin IVA' => $articulo->pventa_sin_iva,
                    'Precio venta con IVA' => $articulo->pventa_con_iva,
                    'Margen %' => $margenPct,
                    'Descuento' => $articulo->descuento,
                    'Tipo producto' => $articulo->tipo_producto_id == 1 ? 'Simple' : 'Personalizado',
                    'Marca' => optional($articulo->marca)->nombre ?? '',
                    'Pesable' => $articulo->articulo_pesable_balanza ? 'Sí' : 'No',
                    'IVA compra' => optional($articulo->ivaCompra)->tipo_iva ?? '',
                    'IVA venta' => optional($articulo->ivaVenta)->tipo_iva ?? '',
                    'Estado' => $articulo->estado,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Codigo',
            'Nombre',
            'Descripcion',
            'Stock',
            'Precio compra sin IVA',
            'Precio compra con IVA',
            'Precio venta sin IVA',
            'Precio venta con IVA',
            'Margen %',
            'Descuento',
            'Tipo producto',
            'Marca',
            'Pesable',
            'IVA compra',
            'IVA venta',
            'Estado',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 40,
            'C' => 50,
            'D' => 12,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 12,
            'J' => 15,
            'K' => 20,
            'L' => 20,
            'M' => 15,
            'N' => 20,
            'O' => 20,
            'P' => 15,
        ];
    }
}