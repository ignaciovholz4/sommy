<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperacionCambio extends Model
{
    protected $table = 'operaciones_cambio';

    protected $fillable = [
        'tipo',
        'moneda_id',
        'cuenta_ars_id',
        'cuenta_moneda_id',
        'monto_moneda',
        'cotizacion',
        'monto_ars',
        'fecha',
        'observaciones',
        'movimiento_ars_id',
        'movimiento_moneda_id',
        'disponible',
        'resultado',
        'creado_por',
        'referencia_type',
        'referencia_id',
    ];

    protected $casts = [
        'monto_moneda' => 'decimal:2',
        'cotizacion' => 'decimal:4',
        'monto_ars' => 'decimal:2',
        'disponible' => 'decimal:2',
        'resultado' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    public function cuentaArs()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_ars_id');
    }

    public function cuentaMoneda()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_moneda_id');
    }

    public function movimientoArs()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_ars_id');
    }

    public function movimientoMoneda()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_moneda_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(\App\User::class, 'creado_por');
    }

    /** Consumos de este lote de compra (ventas que lo usaron como costo FIFO). */
    public function consumos()
    {
        return $this->hasMany(OperacionCambioConsumo::class, 'compra_id');
    }

    /** Lotes de compra que cubrieron esta venta. */
    public function coberturas()
    {
        return $this->hasMany(OperacionCambioConsumo::class, 'venta_id');
    }
}
