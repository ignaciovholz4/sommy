<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SucursalArticulo extends Model
{
    use HasFactory;

    protected $table = 'sucursal_articulo';

    protected $fillable = [
        'sucursal_id',
        'articulo_id',
        'stock',
        'ubicacion',
        'activo',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id', 'idarticulo');
    }
}

