<?php

namespace App\Exports;

use App\Models\SucursalArticulo;
use App\Models\SucursalCombinacion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

/** Stock de una sucursal (artículos simples + combinaciones/variantes), tal como se ve en /sucursal/{id}/stock. */
class SucursalStockExport implements FromCollection, WithHeadings, WithColumnWidths
{
    public function __construct(protected int $sucursalId)
    {
    }

    public function collection()
    {
        return static::filas($this->sucursalId);
    }

    /** Reutilizado también por el PDF (SucursalArticuloController::exportPdf). */
    public static function filas(int $sucursalId)
    {
        $simples = SucursalArticulo::with('articulo')
            ->where('sucursal_id', $sucursalId)
            ->where('activo', 1)
            ->get()
            ->map(fn ($sa) => [
                'Artículo' => optional($sa->articulo)->nombre ?? '—',
                'Combinación' => '—',
                'Código' => optional($sa->articulo)->codigo ?? '—',
                'Stock actual' => (float) $sa->stock,
                'Stock mínimo' => $sa->stock_minimo ?? '',
                'Ubicación' => $sa->ubicacion ?? '',
            ]);

        $combinaciones = SucursalCombinacion::with(['combinacion.producto'])
            ->where('sucursal_id', $sucursalId)
            ->where('activo', 1)
            ->get()
            ->map(function ($sc) {
                $producto = optional($sc->combinacion)->producto;
                return [
                    'Artículo' => optional($producto)->nombre ?? '—',
                    'Combinación' => optional($sc->combinacion)->combinacion ?? '—',
                    'Código' => optional($producto)->codigo ?? '—',
                    'Stock actual' => (float) $sc->stock,
                    'Stock mínimo' => '',
                    'Ubicación' => $sc->ubicacion ?? '',
                ];
            });

        return $simples->concat($combinaciones)->sortBy('Artículo')->values();
    }

    public function headings(): array
    {
        return ['Artículo', 'Combinación', 'Código', 'Stock actual', 'Stock mínimo', 'Ubicación'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 18,
            'C' => 18,
            'D' => 14,
            'E' => 14,
            'F' => 22,
        ];
    }
}
