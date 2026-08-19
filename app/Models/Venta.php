<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';
    protected $primaryKey = 'idventa';

    public $timestamps = true;

    protected $fillable = [
        "user_id",
        "cliente_id",
        "revendedor_id",
        "tipo_comprobante_id",
        "tipo_venta",
        "sucursal_id",
        "num_folio",
        "fecha",
        "estado",
        "total_neto",
        "total_con_iva",
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'idcliente');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVentaProducto::class, 'venta_id', 'idventa');
    }

    // Relación con movimientos de caja
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'comprobante', 'num_folio')
                    ->with('cajaApertura.cuenta');
    }

    public function tipoComprobante()
    {
        return $this->belongsTo(TipoComprobante::class, 'tipo_comprobante_id', 'idtipo_comprobante');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function revendedor()
    {
        return $this->belongsTo(Revendedor::class, 'revendedor_id', 'id');
    }

    /**
     * ✅ Accessor para IVA discriminado
     */
    public function getIvaDiscriminadoAttribute()
    {
        $iva = [];

        foreach ($this->detalles as $detalle) {
            $porcentaje = $detalle->iva;
            $monto = $detalle->subtotal_neto * ($porcentaje / 100);

            if (!isset($iva[$porcentaje])) {
                $iva[$porcentaje] = 0;
            }
            $iva[$porcentaje] += $monto;
        }

        return $iva;
    }
}