<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoCompra extends Model
{
    protected $table = 'pedidos_compra';

    protected $fillable = [
        'user_id',
        'proveedor_id',
        'sucursal_id',
        'tipo_comprobante_id',
        'num_folio',
        'fecha',
        'estado',
        'origen',
        'a_credito',
        'observaciones',
        'compra_id',
        'total_neto',
        'total_con_iva',
    ];

    protected $casts = [
        'a_credito' => 'boolean',
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedidoCompra::class, 'pedido_compra_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'idproveedor');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function tipoComprobante()
    {
        return $this->belongsTo(TipoComprobante::class, 'tipo_comprobante_id', 'idtipo_comprobante');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id', 'idcompra');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    /**
     * IVA discriminado por alícuota: [porcentaje => monto]
     */
    public function getIvaDiscriminadoAttribute(): array
    {
        $result = [];

        foreach ($this->detalles as $d) {
            $monto = $d->subtotal_con_iva - $d->subtotal_neto;
            $key = rtrim(rtrim(number_format((float) $d->iva, 2, '.', ''), '0'), '.');
            $result[$key] = ($result[$key] ?? 0) + $monto;
        }

        return $result;
    }
}
