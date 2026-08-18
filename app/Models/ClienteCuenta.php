<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Cuenta de comprador de la tienda online.
 * Usa la misma tabla `clientes` del sistema: un cliente con password puede iniciar sesión.
 */
class ClienteCuenta extends Authenticatable
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
}
