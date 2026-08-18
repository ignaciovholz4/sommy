<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaApertura extends Model
{
    protected $table = 'caja_aperturas';

    protected $fillable = [
        'cuenta_id',
        'fecha_apertura',
        'fecha_cierre',
        'fondo_inicial',
        'abierta',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre'   => 'datetime',
        'abierta'        => 'boolean',
        'fondo_inicial'  => 'decimal:2',
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    // Helpers de estado
    public function estaAbierta(): bool
    {
        return $this->abierta === true && $this->fecha_cierre === null;
    }
}