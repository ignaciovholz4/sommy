<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SucursalCombinacion extends Model
{
    protected $table = 'sucursal_combinacion';
    protected $fillable = ['sucursal_id', 'combinacion_id', 'stock', 'ubicacion', 'activo'];

    public function combinacion()
    {
        return $this->belongsTo(ProductoCombinacion::class, 'combinacion_id', 'idcombinacion');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

}