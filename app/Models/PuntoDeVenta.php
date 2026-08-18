<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuntoDeVenta extends Model
{
    use HasFactory; 
    protected $table = 'puntos_venta'; 
    protected $fillable = [ 'sucursal_id', 'nombre', 'codigo', 'activo', ]; 
    
    // Punto de venta pertenece a una sucursal 
    public function sucursal() { 
        return $this->belongsTo(Sucursal::class, 'sucursal_id'); 
    }
}
