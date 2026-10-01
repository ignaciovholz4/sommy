<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Imagen de la vidriera de un combo (tabla combo_imagenes). La primera
 * (menor orden) es la que se muestra en la card del combo, en lugar de la
 * imagen del producto suelto.
 */
class ComboImagen extends Model
{
    protected $table = 'combo_imagenes';

    protected $fillable = [
        'producto_id',
        'path',
        'orden',
        'alt',
    ];

    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'producto_id', 'idarticulo');
    }
}
