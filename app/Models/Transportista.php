<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transportista extends Model
{
    protected $table = 'transportistas';

    protected $fillable = [
        'nombre',
        'cuit',
        'telefono',
        'email',
        'notas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function envios()
    {
        return $this->hasMany(Envio::class, 'transportista_id');
    }
}
