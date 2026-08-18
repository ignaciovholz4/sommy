<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'idproveedor';
    public $timestamps = false;
    protected $fillable = ['nombre', 'direccion', 'telefono', 'email', 'estado', 'cuit', 'condicion_pago_dias', 'notas'];

    public function compras()
    {
        return $this->hasMany(Compra::class, 'proveedor_id', 'idproveedor');
    }

    // Cuenta corriente del proveedor (cuentas por pagar)
    public function ccMovimientos()
    {
        return $this->hasMany(ProveedorCcMovimiento::class, 'proveedor_id', 'idproveedor');
    }

    /**
     * Saldo de cuenta corriente: lo que le debemos al proveedor (debe - haber).
     */
    public function saldoCc(): float
    {
        $debe  = (float) $this->ccMovimientos()->where('tipo', 'debe')->sum('monto');
        $haber = (float) $this->ccMovimientos()->where('tipo', 'haber')->sum('monto');

        return round($debe - $haber, 2);
    }
}
