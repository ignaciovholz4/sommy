<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes_aprobacion';

    protected $fillable = [
        'tipo',
        'descripcion',
        'datos',
        'origen_type',
        'origen_id',
        'solicitante_id',
        'estado',
        'aprobado_por',
        'resuelto_at',
        'motivo_rechazo',
    ];

    protected $casts = [
        'datos' => 'array',
        'resuelto_at' => 'datetime',
    ];

    public function origen()
    {
        return $this->morphTo();
    }

    public function solicitante()
    {
        return $this->belongsTo(\App\User::class, 'solicitante_id');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(\App\User::class, 'aprobado_por');
    }
}
