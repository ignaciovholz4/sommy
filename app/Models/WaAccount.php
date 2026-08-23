<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaAccount extends Model
{
    protected $table = 'wa_accounts';

    protected $fillable = [
        'nombre', 'channel', 'provider', 'phone_number_id', 'waba_id', 'page_id', 'ig_account_id',
        'display_phone', 'token', 'verify_token', 'app_secret', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'token' => 'encrypted',
        'app_secret' => 'encrypted',
    ];

    public function conversations()
    {
        return $this->hasMany(WaConversation::class, 'wa_account_id');
    }

    public function templates()
    {
        return $this->hasMany(WaTemplate::class, 'wa_account_id');
    }
}
