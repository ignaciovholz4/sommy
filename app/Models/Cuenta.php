<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    protected $table = 'cuentas';

    protected $fillable = [
        'nombre',
        'sucursal_id',
        'moneda_id',
        'tipo',   // 'caja', 'banco' o 'tercero'
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function moneda()
    {
        return $this->belongsTo(Moneda::class);
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'cuenta_id');
    }

    public function aperturas()
    {
        return $this->hasMany(CajaApertura::class, 'cuenta_id');
    }

    public function chytapayConexion()
    {
        return $this->hasOne(ChytapayConexion::class, 'cuenta_id');
    }

    // Helpers de tipo
    public function esCaja(): bool
    {
        return $this->tipo === 'caja';
    }

    public function esBanco(): bool
    {
        return $this->tipo === 'banco';
    }

    public function esTercero(): bool
    {
        return $this->tipo === 'tercero';
    }
}