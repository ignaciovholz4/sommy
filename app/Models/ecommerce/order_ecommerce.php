<?php

namespace App\Models\ecommerce;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente; // importa el modelo de clientes

class order_ecommerce extends Model
{
    use HasFactory;

    protected $table = 'order_ecommerce';

    protected $primaryKey = "order_id";

    public $timestamps = false;

    protected $fillable = [
        'status_order_id',
        'cliente_id',
        'origen',
        'fecha_entrega',
        'subtotal_amount',
        'total_amount',
        'additional_info',
        'order_date',
        'zona_envio_id',
        'costo_envio',
        'descuento_pago',
        'direccion_localidad',
        'direccion_provincia',
        'direccion_cp',
    ];

    // 🔗 Relación con el estado de la orden
    public function status()
    {
        return $this->belongsTo(status_order_ecommerce::class, 'status_order_id', 'status_id');
    }

    // 🔗 Relación con el cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'idcliente');
    }

    // 🔗 Relación con los detalles de la orden
    public function detalles()
    {
        return $this->hasMany(order_detail_ecommerce::class, 'order_ecommerce_id', 'order_id');
    }

    // 🔗 Relación con el pago
    public function pago()
    {
        return $this->hasOne(payment_ecommerce::class, 'order_id', 'order_id');
    }

    public function asignaciones()
    {
        return $this->hasMany(order_stock_asignacion::class, 'order_id', 'order_id');
    }
}