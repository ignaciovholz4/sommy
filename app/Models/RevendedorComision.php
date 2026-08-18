<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ecommerce\order_ecommerce;

class RevendedorComision extends Model
{
    protected $table = 'revendedor_comisiones';

    protected $fillable = [
        'revendedor_id', 'order_id', 'venta_id', 'monto_venta', 'porcentaje',
        'comision', 'estado', 'pago_id', 'pagada_at',
    ];

    protected $casts = [
        'monto_venta' => 'float',
        'porcentaje' => 'float',
        'comision' => 'float',
        'pagada_at' => 'datetime',
    ];

    public function revendedor()
    {
        return $this->belongsTo(Revendedor::class, 'revendedor_id');
    }

    public function order()
    {
        return $this->belongsTo(order_ecommerce::class, 'order_id', 'order_id');
    }

    /** Venta manual del mostrador (cuando la comisión no viene del ecommerce) */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'idventa');
    }
}
