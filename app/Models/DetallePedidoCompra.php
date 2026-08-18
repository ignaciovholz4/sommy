<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedidoCompra extends Model
{
    protected $table = 'detalle_pedidos_compra';

    protected $fillable = [
        'pedido_compra_id',
        'articulo_id',
        'combinacion_id',
        'tipo_producto_id',
        'price_list_id',
        'cantidad',
        'precio_unitario',
        'descuento',
        'iva',
        'subtotal_neto',
        'subtotal_con_iva',
    ];

    public function pedido()
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
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
