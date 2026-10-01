<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Carga las fotos de combo armado (colchón + base + almohadas) de Cielo, Nube
 * y Eclipse como primera imagen de sus combos, para que la vidriera de combos
 * no muestre la misma foto que el producto suelto. Los archivos viajan en el
 * repo (public/imagenes/combos), así que esto sólo inserta las filas.
 */
return new class extends Migration
{
    private array $mapa = [
        'cielo'   => 'imagenes/combos/combo-cielo.jpg',
        'nube'    => 'imagenes/combos/combo-nube.jpg',
        'eclipse' => 'imagenes/combos/combo-eclipse.jpg',
    ];

    public function up(): void
    {
        foreach ($this->mapa as $clave => $path) {
            if (!File::exists(public_path($path))) {
                continue;
            }

            $producto = DB::table('productos')
                ->where('estado', 'Activo')
                ->where('combo_descuento_pct', '>', 0)
                ->where('nombre', 'like', '%' . $clave . '%')
                ->orderBy('idarticulo')
                ->first(['idarticulo', 'nombre']);

            if (!$producto) {
                continue;
            }

            $yaEsta = DB::table('combo_imagenes')
                ->where('producto_id', $producto->idarticulo)
                ->where('path', $path)
                ->exists();

            if ($yaEsta) {
                continue;
            }

            // Va primera: el resto de la galería del combo se corre un lugar.
            DB::table('combo_imagenes')
                ->where('producto_id', $producto->idarticulo)
                ->increment('orden');

            DB::table('combo_imagenes')->insert([
                'producto_id' => $producto->idarticulo,
                'path'        => $path,
                'orden'       => 0,
                'alt'         => 'Combo ' . $producto->nombre,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('combo_imagenes')->whereIn('path', array_values($this->mapa))->delete();
    }
};
