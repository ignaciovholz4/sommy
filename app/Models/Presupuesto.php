<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    // Nombre de la tabla
    protected $table = 'presupuestos';

    // Primary key
    protected $primaryKey = 'idpresupuesto';

    // Si la PK es autoincremental y de tipo int (default)
    public $incrementing = true;
    protected $keyType = 'int';

    // Timestamps (si los usás en la tabla)
    public $timestamps = true;

    protected $fillable = [
        'idcliente',
        'fecha',
        'num_folio',
        'estado',
        'total',
        'total_neto',
        'total_con_iva',
        'sucursal_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idcliente');
    }

    public function detalles()
    {
        return $this->hasMany(PresupuestoDetalle::class, 'presupuesto_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Accessor: devuelve IVA discriminado agrupado por porcentaje
     */
    public function getIvaDiscriminadoAttribute()
    {
        $iva = [];

        foreach ($this->detalles as $detalle) {
            $montoIva = $detalle->subtotal_con_iva - $detalle->subtotal_neto;
            $porcentaje = $detalle->iva;

            if (!isset($iva[$porcentaje])) {
                $iva[$porcentaje] = 0;
            }
            $iva[$porcentaje] += $montoIva;
        }

        return $iva;
    }

    public function getTotalNetoFormattedAttribute()
    {
        return number_format($this->total_neto, 2, ',', '.');
    }

    public function getTotalConIvaFormattedAttribute()
    {
        return number_format($this->total_con_iva, 2, ',', '.');
    }
}