<?php

namespace App\Models\ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Articulo; // modelo de productos
use App\Models\ecommerce\order_ecommerce;
use App\Models\ProductoCombinacion;

class order_detail_ecommerce extends Model
{
    use HasFactory;

    protected $table = 'order_detail_ecommerce';

    protected $primaryKey = "order_detail_id";

    public $timestamps = false;

    protected $fillable = [
        'order_ecommerce_id',
        'product_id',
        'quantity',
        'price',
        'total',
        'producto_variacion_variante_id',
    ];

    // 🔗 Relación con la orden principal
    public function order()
    {
        return $this->belongsTo(order_ecommerce::class, 'order_ecommerce_id', 'order_id');
    }

    // 🔗 Relación con el producto
    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'product_id', 'idarticulo');
    }

    // 🔗 Relación con la combinación (si aplica)
    public function combinacion()
    {
        return $this->belongsTo(ProductoCombinacion::class, 'producto_variacion_variante_id', 'idcombinacion');
    }
}