<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Cuenta de comprador de la tienda online.
 * Usa la misma tabla `clientes` del sistema: un cliente con password puede iniciar sesión.
 * Tiene que verificar el correo antes de poder finalizar una compra (ver EnsureClienteVerified).
 */
class ClienteCuenta extends Authenticatable implements MustVerifyEmailContract
{
    use Notifiable;

    protected $table = 'clientes';
    protected $primaryKey = 'idcliente';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'materno', 'paterno', 'email', 'password', 'telefono',
        'direccion', 'localidad', 'provincia', 'codigo_postal', 'estatus',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyClienteEmail());
    }
}
