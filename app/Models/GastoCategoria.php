<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoCategoria extends Model
{
    protected $table = 'gasto_categorias';

    protected $fillable = [
        'nombre',
        'padre_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function padre()
    {
        return $this->belongsTo(GastoCategoria::class, 'padre_id');
    }

    public function hijas()
    {
        return $this->hasMany(GastoCategoria::class, 'padre_id');
    }

    public function gastos()
    {
        return $this->hasMany(Gasto::class, 'gasto_categoria_id');
    }
}
