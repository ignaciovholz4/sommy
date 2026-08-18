<?php

namespace App\Models\variacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto_variante extends Model
{
    use HasFactory;

    protected $table='producto_variacion_variante';

    protected $primaryKey="id";

    public $timestamps=false;

    protected $fillable = [
        'color_id',
        'product_integration_id',
        'price',
        'name_image',
        'path_image',
        'stock',
        'active',
        'show_ecommerce',
        'pcompra',
    ];
}
