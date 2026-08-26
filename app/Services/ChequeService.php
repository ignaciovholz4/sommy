<?php

namespace App\Services;

use App\Models\Cheque;
use App\Models\Movimiento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Libro subsidiario de cheques (propios y de terceros). No toca la
 * contabilidad de Movimiento: solo hace seguimiento de custodia/vencimiento
 * de los cheques que ya se registraron como medio de pago en Ventas,
 * Pedidos, Compras, Gastos o CxP.
 */
class ChequeService
{
    /** Cheque de un cliente que Sommy recibe como pago (Ventas, Pedidos). */
    public function registrarRecibido(array $datos, Model $origen, Movimiento $movimiento): Cheque
    {
        return $this->crear('tercero', $datos, $origen, $movimiento);
    }

    /** Cheque propio que Sommy emite para pagarle a un proveedor (Compras, Gastos, CxP). */
    public function registrarPropio(array $datos, Model $origen, Movimiento $movimiento): Cheque
    {
        return $this->crear('propio', $datos, $origen, $movimiento);
    }

    private function crear(string $tipo, array $datos, Model $origen, Movimiento $movimiento): Cheque
    {
        return Cheque::create([
            'tipo' => $tipo,
            'numero' => $datos['numero'] ?? null,
            'banco_emisor' => $datos['banco_emisor'] ?? null,
            'contraparte_nombre' => $datos['contraparte_nombre'] ?? null,
            'contraparte_cuit' => $datos['contraparte_cuit'] ?? null,
            'monto' => $datos['monto'],
            'fecha_emision' => $datos['fecha_emision'] ?? now(),
            'fecha_cobro' => $datos['fecha_cobro'],
            'estado' => 'en_cartera',
            'movimiento_id' => $movimiento->id,
            'origen_type' => get_class($origen),
            'origen_id' => $origen->getKey(),
            'observaciones' => $datos['observaciones'] ?? null,
            'creado_por' => Auth::id(),
        ]);
    }

    /**
     * Interpreta un valor "cheque-{id}" usado en los selectores de cuenta/destino
     * de pago (Compras, Gastos, CxP) como endoso de un cheque de tercero en cartera.
     */
    public function resolverEndoso(string $destino): ?Cheque
    {
        if (!preg_match('/^cheque-(\d+)$/', $destino, $m)) {
            return null;
        }

        return Cheque::where('id', (int) $m[1])
            ->where('tipo', 'tercero')
            ->where('estado', 'en_cartera')
            ->first();
    }

    /** Marca un cheque de tercero como entregado a un proveedor (endoso), ligado al egreso que pagó. */
    public function entregar(Cheque $cheque, Movimiento $movimiento): void
    {
        $cheque->update([
            'estado' => 'entregado',
            'movimiento_entrega_id' => $movimiento->id,
        ]);
    }
}
