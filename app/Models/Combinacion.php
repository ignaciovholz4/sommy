<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Combinacion extends Model
{
    protected $table = 'combinaciones';
    protected $primaryKey = 'idcombinacion';

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'idarticulo', 'idarticulo');
    }
}
