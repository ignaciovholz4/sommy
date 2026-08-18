<?php

namespace App\Models\variacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variantes_para_variacion extends Model
{
    use HasFactory;

    protected $table = 'variantes_para_variaciones';

    public $timestamps=false;

    protected $primaryKey = 'id';

    protected $fillable = [
        'variacion_id',
        'name',
        'option_type',
        'descripcion',
        'status'
    ];

}
