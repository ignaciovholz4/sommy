<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoDetalle extends Model
{
    protected $table = 'detalles_presupuesto';
    protected $primaryKey = 'id_detalle_presupuesto';

    protected $fillable = [
        'presupuesto_id',
        'idarticulo',
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

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'presupuesto_id');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'idarticulo');
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