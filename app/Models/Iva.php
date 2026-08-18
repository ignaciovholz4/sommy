<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iva extends Model
{
    protected $table = 'iva';
    protected $primaryKey = 'idiva';
    public $timestamps = false;

    protected $fillable = [
        'tipo_iva',     // descripción del IVA (ej. "IVA 21%")
        'value_iva',    // porcentaje numérico (ej. 21.00)
        'value_arca'    // identificador para API Arca
    ];

    // Relación inversa con artículos que usan este IVA en compra
    public function articulosCompra()
    {
        return $this->hasMany(Articulo::class, 'iva_compra_id', 'idiva');
    }

    // Relación inversa con artículos que usan este IVA en venta
    public function articulosVenta()
    {
        return $this->hasMany(Articulo::class, 'iva_venta_id', 'idiva');
    }
}