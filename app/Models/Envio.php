<?php

namespace App\Models;

use App\Models\ecommerce\order_ecommerce;
use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    protected $table = 'envios';

    public const ESTADOS = ['pendiente', 'despachado', 'en_transito', 'entregado', 'fallido'];

    protected $fillable = [
        'tipo',
        'order_ecommerce_id',
        'compra_id',
        'venta_id',
        'transportista_id',
        'costo',
        'pagado_por',
        'estado',
        'fecha_despacho',
        'fecha_entrega_estimada',
        'fecha_entrega_real',
        'tracking',
        'direccion_entrega',
        'notas',
        'gasto_id',
    ];

    protected $casts = [
        'costo'                  => 'decimal:2',
        'fecha_despacho'         => 'datetime',
        'fecha_entrega_estimada' => 'date',
        'fecha_entrega_real'     => 'datetime',
    ];

    public function transportista()
    {
        return $this->belongsTo(Transportista::class, 'transportista_id');
    }

    public function orden()
    {
        return $this->belongsTo(order_ecommerce::class, 'order_ecommerce_id', 'order_id');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id', 'idcompra');
    }

    /** Venta manual del mostrador (tipo venta_manual) */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'idventa');
    }

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }
}
