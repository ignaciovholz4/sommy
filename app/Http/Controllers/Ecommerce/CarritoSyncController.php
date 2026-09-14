<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\ecommerce\ClienteCarrito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sincroniza el carrito (localStorage) del cliente logueado hacia el
 * servidor, solo para poder detectar abandono y avisarle por mail — nunca
 * se guarda nada de un visitante anónimo, no hay a quién escribirle.
 */
class CarritoSyncController extends Controller
{
    public function sync(Request $request)
    {
        $cliente = Auth::guard('cliente')->user();
        if (!$cliente) {
            return response()->json(['ok' => false], 200);
        }

        $items = (array) $request->input('items', []);

        if (empty($items)) {
            ClienteCarrito::where('cliente_id', $cliente->getKey())->delete();

            return response()->json(['ok' => true]);
        }

        ClienteCarrito::updateOrCreate(
            ['cliente_id' => $cliente->getKey()],
            [
                'items' => $items,
                'total' => (float) $request->input('total', 0),
                // Cada sincronización es actividad nueva: si ya se había avisado de un
                // abandono previo, se resetea para que un abandono futuro avise de nuevo.
                'aviso_enviado_at' => null,
            ]
        );

        return response()->json(['ok' => true]);
    }
}
