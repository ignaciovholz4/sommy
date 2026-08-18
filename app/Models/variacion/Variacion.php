<?php

namespace App\Models\Variacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variacion extends Model
{
    use HasFactory;
    protected $table='variaciones';

    protected $primaryKey="id";

    public $timestamps=false;

    protected $fillable = [
        'name',
        'option_type',
    ];

}
