<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ProveedorCcMovimiento extends Model
{
    protected $table = 'proveedor_cc_movimientos';

    protected $fillable = [
        'proveedor_id',
        'tipo',
        'origen',
        'compra_id',
        'movimiento_id',
        'monto',
        'fecha_vencimiento',
        'estado',
        'descripcion',
        'user_id',
    ];

    protected $casts = [
        'monto'             => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'idproveedor');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id', 'idcompra');
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Reimputa FIFO los pagos (haber) contra las deudas (debe) de un proveedor
     * y actualiza el estado de cada fila debe: pendiente / parcial / pagado.
     *
     * No usa tabla de imputaciones: recorre las deudas ordenadas por vencimiento
     * (las sin vencimiento al final) y va "consumiendo" el total del haber.
     */
    public static function reimputarFifo(int $proveedorId): void
    {
        $totalHaber = (float) static::where('proveedor_id', $proveedorId)
            ->where('tipo', 'haber')
            ->sum('monto');

        $deudas = static::where('proveedor_id', $proveedorId)
            ->where('tipo', 'debe')
            ->orderByRaw('fecha_vencimiento IS NULL, fecha_vencimiento asc, id asc')
            ->get();

        foreach ($deudas as $deuda) {
            $monto = (float) $deuda->monto;

            if ($totalHaber >= $monto - 0.009) {
                $nuevoEstado = 'pagado';
                $totalHaber -= $monto;
            } elseif ($totalHaber > 0.009) {
                $nuevoEstado = 'parcial';
                $totalHaber = 0;
            } else {
                $nuevoEstado = 'pendiente';
            }

            if ($deuda->estado !== $nuevoEstado) {
                $deuda->estado = $nuevoEstado;
                $deuda->save();
            }
        }
    }
}
