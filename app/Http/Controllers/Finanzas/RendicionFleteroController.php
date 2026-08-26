<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\CajaApertura;
use App\Models\Movimiento;
use App\Models\Transportista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Cierra el circuito que entregas_fletero.rendido dejo pensado: el efectivo
 * que un fletero cobro en la puerta queda "a rendir" hasta que se registra
 * como ingreso real en una caja o banco, todo junto por fletero.
 */
class RendicionFleteroController extends Controller
{
    public function rendir(Request $request, Transportista $transportista)
    {
        Gate::authorize('haveaccess', 'finanzas.transportistas.rendir');

        $request->validate([
            'destino' => 'required|string',
            'medio'   => 'nullable|string|max:30',
        ]);

        $pendiente = (float) DB::table('entregas_fletero')
            ->where('transportista_id', $transportista->id)->where('rendido', false)
            ->sum('monto_cobrado');

        if ($pendiente <= 0) {
            return response()->json(['estado' => 0, 'mensaje' => 'Este fletero no tiene efectivo pendiente de rendir.']);
        }

        $cuentaId = null;
        $aperturaId = null;
        $efectivo = 0;
        $bancos = 0;
        $medio = $request->medio ?: 'efectivo';

        if (str_starts_with($request->destino, 'caja-')) {
            $aperturaId = (int) str_replace('caja-', '', $request->destino);
            $apertura = CajaApertura::findOrFail($aperturaId);
            if (!$apertura->estaAbierta()) {
                return response()->json(['estado' => 0, 'mensaje' => 'La caja seleccionada no está abierta.']);
            }
            $cuentaId = $apertura->cuenta_id;
            $efectivo = $medio === 'efectivo' ? $pendiente : 0;
            $bancos = $medio !== 'efectivo' ? $pendiente : 0;
        } elseif (str_starts_with($request->destino, 'banco-')) {
            $cuentaId = (int) str_replace('banco-', '', $request->destino);
            $bancos = $pendiente;
        } else {
            return response()->json(['estado' => 0, 'mensaje' => 'Destino inválido.']);
        }

        $movimiento = Movimiento::create([
            'cuenta_id'         => $cuentaId,
            'caja_apertura_id'  => $aperturaId,
            'fecha'             => now(),
            'tipo'              => 'ingreso',
            'medio'             => $medio,
            'cliente_proveedor' => $transportista->nombre,
            'comprobante'       => 'Rendición fletero ' . $transportista->nombre . ' ' . now()->format('d/m/Y'),
            'observaciones'     => 'Efectivo cobrado en entregas, rendido a la caja/banco.',
            'efectivo'          => $efectivo,
            'bancos'            => $bancos,
            'total'             => $pendiente,
        ]);

        DB::table('entregas_fletero')
            ->where('transportista_id', $transportista->id)->where('rendido', false)
            ->update(['rendido' => true, 'rendido_movimiento_id' => $movimiento->id]);

        return response()->json([
            'estado'  => 1,
            'mensaje' => 'Se rindieron $' . number_format($pendiente, 2, ',', '.') . ' de ' . $transportista->nombre . '.',
        ]);
    }
}
