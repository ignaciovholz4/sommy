<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    protected $table = 'cheques';

    protected $fillable = [
        'tipo',
        'numero',
        'banco_emisor',
        'contraparte_nombre',
        'contraparte_cuit',
        'monto',
        'fecha_emision',
        'fecha_cobro',
        'estado',
        'cuenta_id',
        'movimiento_id',
        'movimiento_entrega_id',
        'origen_type',
        'origen_id',
        'adjunto_path',
        'adjunto_nombre',
        'observaciones',
        'creado_por',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_cobro' => 'date',
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id');
    }

    public function movimientoEntrega()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_entrega_id');
    }

    public function origen()
    {
        return $this->morphTo();
    }

    public function creadoPor()
    {
        return $this->belongsTo(\App\User::class, 'creado_por');
    }

    public function estaVencido(): bool
    {
        return in_array($this->estado, ['en_cartera', 'depositado'])
            && $this->fecha_cobro->lessThan(now()->startOfDay());
    }
}
