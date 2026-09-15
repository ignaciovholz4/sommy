<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClarityInsight extends Model
{
    protected $table = 'clarity_insights';

    protected $fillable = ['fecha', 'payload', 'sincronizado_at'];

    protected $casts = [
        'fecha' => 'date',
        'payload' => 'array',
        'sincronizado_at' => 'datetime',
    ];
}
