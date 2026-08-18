<?php

namespace App\Models\variacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto_integracion_variante extends Model
{
    use HasFactory;

    protected $table='producto_integracion_variante';

    protected $primaryKey="id";

    public $timestamps=false;

    protected $fillable = [
        'producto_id',
        'variacion_id',
        'variante_id',
        'descripcion',
        'status',
        'activo',
    ];
}
