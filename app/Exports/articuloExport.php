<?php

namespace App\Exports;

use App\Models\Articulo;
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
        return Articulo::select(
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
            ->map(function ($articulo) {
                return [
                    'Codigo' => $articulo->codigo,
                    'Nombre' => $articulo->nombre,
                    'Descripcion' => $articulo->descripcion,
                    'Precio compra sin IVA' => $articulo->pcompra_sin_iva,
                    'Precio compra con IVA' => $articulo->pcompra_con_iva,
                    'Precio venta sin IVA' => $articulo->pventa_sin_iva,
                    'Precio venta con IVA' => $articulo->pventa_con_iva,
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
            'Precio compra sin IVA',
            'Precio compra con IVA',
            'Precio venta sin IVA',
            'Precio venta con IVA',
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
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 15,
            'I' => 20,
            'J' => 20,
            'K' => 15,
            'L' => 20,
            'M' => 20,
            'N' => 15,
        ];
    }
}