<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompraProducto extends Model
{
    protected $table = 'detalle_compras';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'compra_id',
        'articulo_id',
        'combinacion_id',
        'tipo_producto_id',
        'cantidad',
        'precio_unitario',
        'descuento',
        'iva',
        'subtotal_neto',
        'subtotal_con_iva',
        'price_list_id',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id', 'idcompra');
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