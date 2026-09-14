<?php

namespace App\Models\ecommerce;

use Illuminate\Database\Eloquent\Model;

class ClienteCarrito extends Model
{
    protected $table = 'cliente_carritos';

    protected $fillable = [
        'cliente_id',
        'items',
        'total',
        'aviso_enviado_at',
    ];

    protected $casts = [
        'items' => 'array',
        'aviso_enviado_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(\App\Models\ClienteCuenta::class, 'cliente_id', 'idcliente');
    }
}
