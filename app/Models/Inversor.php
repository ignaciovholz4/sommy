<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inversor extends Model
{
    protected $table = 'inversores';

    protected $fillable = [
        'nombre',
        'porcentaje_participacion',
        'telefono',
        'email',
        'activo',
    ];

    protected $casts = [
        'porcentaje_participacion' => 'decimal:2',
        'activo'                   => 'boolean',
    ];

    public function movimientos()
    {
        return $this->hasMany(InversorMovimiento::class);
    }
}
