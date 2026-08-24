<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoImportado extends Model
{
    protected $table = 'movimientos_bancarios_importados';

    protected $fillable = [
        'cuenta_id',
        'archivo_nombre',
        'archivo_hash',
        'fecha',
        'tipo',
        'monto',
        'descripcion',
        'referencia',
        'fila_original',
        'estado',
        'movimiento_id',
        'conciliado_por',
        'conciliado_at',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'monto'         => 'decimal:2',
        'fila_original' => 'array',
        'conciliado_at' => 'datetime',
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id');
    }

    public function conciliadoPor()
    {
        return $this->belongsTo(\App\User::class, 'conciliado_por');
    }
}
