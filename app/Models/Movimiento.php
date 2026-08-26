<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $fillable = [
        'cuenta_id',
        'caja_apertura_id', // solo aplica si la cuenta es de tipo caja
        'fecha',
        'tipo',
        'medio',
        'alias_tercero',
        'cuit_tercero',
        'cliente_proveedor',
        'comprobante',
        'observaciones',
        'adjunto_path',
        'adjunto_nombre',
        'efectivo',
        'bancos',
        'tarjetas',
        'cheques',
        'total',
        'cotizacion',
        'total_ars',
        'referencia_type',
        'referencia_id',
    ];

    protected $casts = [
        'fecha'    => 'datetime',
        'efectivo' => 'decimal:2',
        'bancos'   => 'decimal:2',
        'tarjetas' => 'decimal:2',
        'cheques' => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    // Relación principal: siempre con Cuenta
    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    // Relación opcional: solo si la cuenta es de tipo caja
    public function apertura()
    {
        return $this->belongsTo(CajaApertura::class, 'caja_apertura_id');
    }

    public function cajaApertura()
    {
        return $this->belongsTo(CajaApertura::class, 'caja_apertura_id');
    }

    // Vínculo polimórfico opcional (Gasto, ProveedorCcMovimiento, etc.)
    public function referencia()
    {
        return $this->morphTo('referencia');
    }
}