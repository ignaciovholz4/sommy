<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';
    protected $primaryKey = 'idcompra';

    public $timestamps = true;

    protected $fillable = [
        "user_id",
        "proveedor_id",
        "tipo_comprobante_id",
        "sucursal_id",
        "num_folio",
        "fecha",
        "estado",
        "total_neto",
        "total_con_iva",
    ];

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'idproveedor');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompraProducto::class, 'compra_id', 'idcompra');
    }

    public function adjuntos()
    {
        return $this->hasMany(CompraAdjunto::class, 'compra_id', 'idcompra');
    }

    // Relación con movimientos de caja
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'comprobante', 'num_folio')
                    ->with('cajaApertura.cuenta');
    }

    // Movimientos de cuenta corriente del proveedor generados por esta compra
    public function ccMovimientos()
    {
        return $this->hasMany(ProveedorCcMovimiento::class, 'compra_id', 'idcompra');
    }

    public function tipoComprobante()
    {
        return $this->belongsTo(TipoComprobante::class, 'tipo_comprobante_id', 'idtipo_comprobante');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
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