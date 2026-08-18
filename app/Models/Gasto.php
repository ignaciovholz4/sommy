<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $table = 'gastos';

    protected $fillable = [
        'fecha',
        'gasto_categoria_id',
        'proveedor_id',
        'descripcion',
        'monto',
        'cuenta_id',
        'movimiento_id',
        'comprobante_path',
        'es_recurrente',
        'frecuencia',
        'proximo_vencimiento',
        'user_id',
        'sucursal_id',
        'estado',
    ];

    protected $casts = [
        'fecha'               => 'date',
        'monto'               => 'decimal:2',
        'es_recurrente'       => 'boolean',
        'proximo_vencimiento' => 'date',
    ];

    public function categoria()
    {
        return $this->belongsTo(GastoCategoria::class, 'gasto_categoria_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'idproveedor');
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function envio()
    {
        return $this->hasOne(Envio::class, 'gasto_id');
    }

    public function estaPagado(): bool
    {
        return $this->estado === 'pagado';
    }
}
