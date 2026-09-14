<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAdsCampanaInsight extends Model
{
    protected $table = 'meta_ads_campanas_insights';

    protected $fillable = [
        'meta_campaign_id', 'nombre_campana', 'fecha', 'spend', 'impressions', 'clicks', 'sincronizado_at',
    ];

    protected $casts = [
        'fecha' => 'date',
        'spend' => 'decimal:2',
        'sincronizado_at' => 'datetime',
    ];
}
