<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory; 
    protected $table = 'sucursales'; 
    protected $fillable = [ 'nombre', 'codigo', 'direccion', 'telefono', 'email', 'activo', ]; 
    // Una sucursal tiene muchos puntos de venta 
    public function puntosDeVenta() { 
        return $this->hasMany(PuntoDeVenta::class, 'sucursal_id'); 
    }

    public function articulos() { 
        return $this->hasMany(SucursalArticulo::class); 
    }

    public function combinaciones()
    {
        return $this->hasMany(\App\Models\SucursalCombinacion::class, 'sucursal_id');
    }

}
