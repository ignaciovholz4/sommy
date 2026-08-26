<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperacionCambioConsumo extends Model
{
    protected $table = 'operacion_cambio_consumos';

    protected $fillable = ['venta_id', 'compra_id', 'cantidad', 'costo_ars'];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'costo_ars' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(OperacionCambio::class, 'venta_id');
    }

    public function compra()
    {
        return $this->belongsTo(OperacionCambio::class, 'compra_id');
    }
}
