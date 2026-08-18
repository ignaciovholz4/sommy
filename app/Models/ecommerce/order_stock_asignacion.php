<?php

namespace App\Models\ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Articulo;
use App\Models\ProductoCombinacion;
use App\Models\Sucursal;

class order_stock_asignacion extends Model
{
    use HasFactory;

    protected $table = 'order_stock_asignaciones';

    protected $primaryKey = 'id';

    protected $fillable = [
        'order_id',
        'product_id',
        'combinacion_id',
        'sucursal_id',
        'cantidad',
    ];

    // 🔗 Relación con la orden
    public function order()
    {
        return $this->belongsTo(order_ecommerce::class, 'order_id', 'order_id');
    }

    // 🔗 Relación con el producto
    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'product_id', 'idarticulo');
    }

    // 🔗 Relación con la combinación (nullable)
    public function combinacion()
    {
        return $this->belongsTo(ProductoCombinacion::class, 'combinacion_id', 'idcombinacion');
    }

    // 🔗 Relación con la sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }
}