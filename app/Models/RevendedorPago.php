<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevendedorPago extends Model
{
    protected $table = 'revendedor_pagos';

    protected $fillable = [
        'revendedor_id', 'monto', 'fecha', 'medio',
        'referencia', 'observacion', 'usuario_id',
    ];

    protected $casts = [
        'monto' => 'float',
        'fecha' => 'date',
    ];

    public function revendedor()
    {
        return $this->belongsTo(Revendedor::class, 'revendedor_id');
    }

    public function comisiones()
    {
        return $this->hasMany(RevendedorComision::class, 'pago_id');
    }
}
