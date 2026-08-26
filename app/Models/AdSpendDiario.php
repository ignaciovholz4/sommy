<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSpendDiario extends Model
{
    protected $table = 'ad_spend_diario';

    protected $fillable = ['plataforma', 'fecha', 'monto', 'moneda', 'sincronizado_at'];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'sincronizado_at' => 'datetime',
    ];
}
