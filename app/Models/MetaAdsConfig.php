<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAdsConfig extends Model
{
    protected $table = 'meta_ads_config';

    protected $fillable = [
        'tope_presupuesto_diario', 'tope_presupuesto_total', 'tope_gasto_diario_cuenta', 'actualizado_por',
    ];

    protected $casts = [
        'tope_presupuesto_diario' => 'decimal:2',
        'tope_presupuesto_total' => 'decimal:2',
        'tope_gasto_diario_cuenta' => 'decimal:2',
    ];

    /** Fila unica de configuracion (patron singleton row, igual a Configuracion). */
    public static function actual(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
