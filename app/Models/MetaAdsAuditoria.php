<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaAdsAuditoria extends Model
{
    protected $table = 'meta_ads_auditoria';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'accion', 'meta_campaign_id', 'meta_adset_id', 'nombre_campana',
        'valor_anterior', 'valor_nuevo', 'exitoso', 'error', 'created_at',
    ];

    protected $casts = [
        'valor_anterior' => 'array',
        'valor_nuevo' => 'array',
        'exitoso' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'created_at' => null,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $registro) {
            $registro->created_at ??= now();
        });
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
