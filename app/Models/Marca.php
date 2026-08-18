<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marcas';
    protected $primaryKey = 'idmarca';
    protected $fillable = ['nombre', 'descripcion', 'status'];
    public $timestamps = false;
}
