<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'idcategoria';
    protected $fillable = ['nombre', 'slug', 'descripcion', 'name_imagen'];

    public $timestamps = false;

    protected static function booted()
    {
        static::created(function ($categoria) {
            if (empty($categoria->slug)) {
                $slug = Str::slug($categoria->nombre) ?: 'categoria';
                if (static::where('slug', $slug)->where('idcategoria', '!=', $categoria->idcategoria)->exists()) {
                    $slug .= '-' . $categoria->idcategoria;
                }
                $categoria->slug = $slug;
                $categoria->saveQuietly();
            }
        });
    }
}
