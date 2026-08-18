<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    protected $table = 'unidades';
    protected $primaryKey = 'idunidad';
    protected $fillable = ['nombre', 'nombre_corto', 'decimal', 'status'];
    public $timestamps = false;
}
