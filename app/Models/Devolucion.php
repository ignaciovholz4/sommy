<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    protected $table = 'devoluciones';
    protected $fillable = [
        'tipo',
        'referencia_id',
        'motivo',
        'fecha',
        'monto',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'referencia_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'referencia_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}
