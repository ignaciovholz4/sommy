<?php

namespace App\Models\configuracion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $table='banner_ecommerce';

    protected $primaryKey="banner_id"; 

    public $timestamps=false;

    protected $fillable = [
        'name',
        'name_image',
        'name_image_movil',
        'description',
    ];
}
