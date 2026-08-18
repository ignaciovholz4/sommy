<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoAtributo extends Model
{
    protected $table = 'producto_atributos';
    protected $fillable = ['producto_id', 'nombre'];

    public function variantes()
    {
        return $this->hasMany(ProductoAtributoVariante::class, 'atributo_id');
    }

    public function producto()
    {
        return $this->belongsTo(Articulo::class, 'producto_id');
    }
}
