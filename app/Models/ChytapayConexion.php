<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChytapayConexion extends Model
{
    protected $table = 'chytapay_conexiones';

    protected $fillable = [
        'cuenta_id',
        'id_token',
        'refresh_token',
        'token_expires_at',
        'comercio_nombre',
        'comercio_email',
        'conectado_por',
        'conectado_at',
        'last_sync_at',
    ];

    protected $casts = [
        'id_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'conectado_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_id');
    }

    public function conectadoPor()
    {
        return $this->belongsTo(\App\User::class, 'conectado_por');
    }

    public function tokenVencido(): bool
    {
        return !$this->token_expires_at || now()->greaterThanOrEqualTo($this->token_expires_at);
    }
}
