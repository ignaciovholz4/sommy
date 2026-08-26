<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InversorMovimiento extends Model
{
    protected $table = 'inversor_movimientos';

    protected $fillable = [
        'inversor_id',
        'tipo',
        'monto',
        'concepto',
        'fecha',
        'cuenta_id',
        'movimiento_id',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function inversor()
    {
        return $this->belongsTo(Inversor::class);
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
