<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVentaProducto extends Model
{
    protected $table = 'detalle_ventas';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'venta_id',
        'articulo_id',
        'combinacion_id',
        'tipo_producto_id',   // ✅ viene directo del producto, no se busca en otra tabla
        'cantidad',
        'precio_unitario',
        'descuento',
        'iva',
        'subtotal_neto',
        'subtotal_con_iva',
        'price_list_id',
    ];

    // 🔗 Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'idventa');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id', 'idarticulo');
    }

    public function combinacion()
    {
        return $this->belongsTo(ProductoCombinacion::class, 'combinacion_id', 'idcombinacion');
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class, 'price_list_id');
    }
}