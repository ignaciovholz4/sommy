<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReposicionAjuste extends Model
{
    protected $table = 'reposicion_ajustes';

    protected $fillable = [
        'dias_cobertura_objetivo',
        'ventana_analisis_dias',
        'stock_minimo_default',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public static function actual(): self
    {
        return static::first() ?? static::create([]);
    }
}
