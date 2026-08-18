<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaTemplate extends Model
{
    protected $table = 'wa_templates';

    protected $fillable = [
        'wa_account_id', 'name', 'language', 'category',
        'body_text', 'variables', 'meta_status',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(WaAccount::class, 'wa_account_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('meta_status', 'approved');
    }
}
