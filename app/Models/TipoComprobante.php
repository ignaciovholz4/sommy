<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoComprobante extends Model
{
    use HasFactory;

    protected $table = 'tipos_comprobantes';
    protected $primaryKey = 'idtipo_comprobante';

    protected $fillable = [
        'codigo',
        'descripcion',
    ];

    /**
     * Comprobantes con los que se registran operaciones de venta/compra.
     * Las notas de crédito/débito son documentos de ajuste (los maneja el
     * módulo de devoluciones) y la Factura E es solo exportación: no se
     * ofrecen al cargar una venta, compra o pedido.
     */
    public function scopeOperativos($query)
    {
        return $query->whereIn('codigo', ['FA', 'FB', 'FC', 'TKT']);
    }

    // Relación con ventas
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'tipo_comprobante_id', 'idtipo_comprobante');
    }

    // Relación con compras
    public function compras()
    {
        return $this->hasMany(Compra::class, 'tipo_comprobante_id', 'idtipo_comprobante');
    }
}