<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    protected $table = 'monedas';

    protected $fillable = ['codigo','nombre','simbolo','decimales'];

    public function cajas()
    {
        return $this->hasMany(Caja::class, 'moneda_id');
    }
}