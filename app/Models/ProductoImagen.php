<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagenes';

    protected $fillable = [
        'producto_id',
        'combinacion_id',
        'tipo',
        'path',
        'orden',
        'alt',
        'principal',
    ];

    protected $casts = [
        'principal' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'producto_id', 'idarticulo');
    }

    public function combinacion()
    {
        return $this->belongsTo(ProductoCombinacion::class, 'combinacion_id', 'idcombinacion');
    }
}
