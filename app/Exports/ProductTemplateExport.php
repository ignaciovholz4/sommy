<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProductTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * Ejemplo de datos para mostrar formato
     */
    public function array(): array
    {
        return [
            [
                'Colchón Bardo Ergonomic',
                'Colchones',
                'COL001',
                '250000.00',
                '420000.00',
                'Colchón de resortes pocket con pillow top, ideal para descanso premium',
                '0.00',
                'IVA 21%',
                'IVA 21%',
                '2', // personalizado (medidas como variantes)
                'Bardo',
                'No',
                'Bardo',            // proveedor
                'BD-ERG-001',       // codigo_proveedor
                'pocket',           // tipo_colchon
                'media',            // firmeza
                '2',                // plazas
                '28',               // altura_cm
                '30',               // densidad
                '110',              // peso_max_kg
                '5',                // garantia_anios
                '30',               // noches_prueba
                'Antialérgico',     // certificaciones
                'Jacquard',         // tela
                'Sí',               // pillow_top
            ],
            [
                'Almohada Viscoelástica',
                'Almohadas',
                'ALM002',
                '15000.00',
                '28000.00',
                'Almohada viscoelástica con funda lavable',
                '10.00',
                'IVA 21%',
                'IVA 21%',
                '1', // simple
                'Bardo',
                'No',
                'Bardo',
                'BD-ALM-010',
                '', '', '', '', '', '', '', '', '', '', '', // sin ficha técnica de colchón
            ],
            [
                'Sommier Base Madera 140',
                'Sommiers',
                'SOM003',
                '120000.00',
                '210000.00',
                'Sommier base de madera reforzada',
                '0.00',
                'IVA 21%',
                'IVA 21%',
                '1',
                'Bardo',
                'No',
                'Bardo',
                'BD-SOM-140',
                '', '', '', '', '', '', '', '', '', '', '',
            ],
        ];
    }

    /**
     * Encabezados de columnas
     */
    public function headings(): array
    {
        return [
            'nombre',
            'categoria',
            'codigo',
            'precio_compra_sin_iva',
            'precio_venta_sin_iva',
            'descripcion',
            'descuento',
            'iva_compra',
            'iva_venta',
            'tipo_producto', // 1 = simple, 2 = personalizado
            'marca',
            'pesable', // Sí/No
            'proveedor',
            'codigo_proveedor',
            'tipo_colchon',   // bonell|pocket|espuma|viscoelastico|hibrido
            'firmeza',        // suave|media|firme
            'plazas',         // 1|1.5|2|2.5|king
            'altura_cm',
            'densidad',       // kg/m3
            'peso_max_kg',
            'garantia_anios',
            'noches_prueba',
            'certificaciones',
            'tela',
            'pillow_top',     // Sí/No
        ];
    }

    /**
     * Estilos y comentarios
     */
    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:Y1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ]);

        // Comentarios para cada columna
        $sheet->getComment('A1')->getText()->createTextRun('Nombre del producto (requerido)');
        $sheet->getComment('B1')->getText()->createTextRun('Categoría del producto (requerido)');
        $sheet->getComment('C1')->getText()->createTextRun('Código único del producto (requerido)');
        $sheet->getComment('D1')->getText()->createTextRun('Precio de compra SIN IVA (numérico)');
        $sheet->getComment('E1')->getText()->createTextRun('Precio de venta SIN IVA (numérico)');
        $sheet->getComment('F1')->getText()->createTextRun('Descripción del producto');
        $sheet->getComment('G1')->getText()->createTextRun('Descuento en porcentaje (0-100)');
        $sheet->getComment('H1')->getText()->createTextRun('IVA de compra (usar valores del seeder)');
        $sheet->getComment('I1')->getText()->createTextRun('IVA de venta (usar valores del seeder)');
        $sheet->getComment('J1')->getText()->createTextRun('Tipo de producto: 1 = simple, 2 = personalizado (con medidas/variantes)');
        $sheet->getComment('K1')->getText()->createTextRun('Marca del producto (opcional)');
        $sheet->getComment('L1')->getText()->createTextRun('Pesable en balanza (Sí/No)');
        $sheet->getComment('M1')->getText()->createTextRun('Proveedor (opcional, se crea si no existe)');
        $sheet->getComment('N1')->getText()->createTextRun('Código del producto en la lista del proveedor');
        $sheet->getComment('O1')->getText()->createTextRun('Tipo de colchón: bonell, pocket, espuma, viscoelastico, hibrido');
        $sheet->getComment('P1')->getText()->createTextRun('Firmeza: suave, media, firme');
        $sheet->getComment('Q1')->getText()->createTextRun('Plazas: 1, 1.5, 2, 2.5, king');
        $sheet->getComment('R1')->getText()->createTextRun('Altura del colchón en cm');
        $sheet->getComment('S1')->getText()->createTextRun('Densidad de espuma en kg/m3');
        $sheet->getComment('T1')->getText()->createTextRun('Peso máximo soportado por plaza en kg');
        $sheet->getComment('U1')->getText()->createTextRun('Garantía en años');
        $sheet->getComment('V1')->getText()->createTextRun('Noches de prueba en domicilio');
        $sheet->getComment('W1')->getText()->createTextRun('Certificaciones (texto libre)');
        $sheet->getComment('X1')->getText()->createTextRun('Tela de la cubierta');
        $sheet->getComment('Y1')->getText()->createTextRun('Tiene pillow top (Sí/No)');

        return $sheet;
    }
}
