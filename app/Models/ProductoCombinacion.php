<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoCombinacion extends Model
{
    protected $table = 'producto_combinaciones';
    protected $primaryKey = 'idcombinacion';
    protected $fillable = ['producto_id', 'combinacion', 'sku', 'json_detalle', 'pcompra_variante', 'pventa_variante'];

    protected $casts = [
        'json_detalle' => 'array'
    ];

    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'producto_id');
    }

    public function imagen()
    {
        return $this->hasOne(ProductoImagen::class, 'combinacion_id', 'idcombinacion');
    }
}
