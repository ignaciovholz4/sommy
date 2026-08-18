<?php

namespace App\Models\variacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;
    
    protected $table='color';

    protected $primaryKey="id";

    public $timestamps=false;

    protected $fillable = [
        'name',
        'hexadecimal',
    ];
}
