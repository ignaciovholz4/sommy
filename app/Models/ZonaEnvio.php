<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonaEnvio extends Model
{
    protected $table = 'zonas_envio';

    protected $fillable = ['nombre', 'costo', 'activo', 'orden'];

    protected $casts = [
        'activo' => 'boolean',
        'costo' => 'decimal:2',
    ];

    public function scopeActivas($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }
}
