<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoAtributoVariante extends Model
{
    protected $table = 'producto_atributo_variantes';
    protected $fillable = ['atributo_id', 'valor'];

    public function atributo()
    {
        return $this->belongsTo(ProductoAtributo::class, 'atributo_id');
    }
}
