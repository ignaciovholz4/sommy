<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\ShareController;
use App\Models\ecommerce\order_ecommerce;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EcommercepagoController extends Controller
{
    /**
     * Webhook de MercadoPago. El pago SIEMPRE se reconsulta a la API
     * (nunca se confía en el payload recibido).
     */
    public function webhook(Request $request, MercadoPagoService $mp)
    {
        $tipo = $request->input('type') ?? $request->input('topic');
        $paymentId = $request->input('data.id') ?? $request->input('id');

        Log::info('Webhook MercadoPago recibido', ['type' => $tipo, 'payment_id' => $paymentId]);

        if ($tipo === 'payment' && $paymentId) {
            $mp->procesarPago((string) $paymentId);
        }

        // MP reintenta si no recibe 200/201
        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Página de agradecimiento post-pedido (back_url de MP y transferencia).
     */
    public function gracias($orderId, MercadoPagoService $mp)
    {
        $order = order_ecommerce::with(['cliente', 'pago', 'detalles.producto'])->find($orderId);

        if (!$order) {
            abort(404);
        }

        // Si vuelve de MP con payment_id en la URL, procesarlo al toque (útil si el webhook no llegó)
        if (request('payment_id')) {
            $mp->procesarPago((string) request('payment_id'));
            $order->load('pago');
        }

        $config = DB::table('configuracion')->first();
        $arrayEmpresa = ShareController::getEmpresaImage();
        $getCategoryLimit = ShareController::getLimitCategory();

        $metodoPago = DB::table('payment_methods')
            ->where('payment_method_id', optional($order->pago)->payment_method_id)
            ->value('method_name') ?? 'transferencia';

        return view('ecommerce.order.gracias', compact('order', 'config', 'arrayEmpresa', 'getCategoryLimit', 'metodoPago'));
    }

    /**
     * Verificación manual del estado del pago (botón en la página de gracias;
     * cubre el caso de webhook que no llega, ej. entorno local).
     */
    public function verificarPago($orderId, MercadoPagoService $mp)
    {
        $order = order_ecommerce::with('pago')->find($orderId);

        if (!$order) {
            return response()->json(['status' => 0, 'message' => 'Pedido no encontrado']);
        }

        $estado = $mp->verificarPagoDePedido($order);
        $order->load('pago');

        return response()->json([
            'status' => 1,
            'mp_status' => $estado,
            'pagado' => optional($order->pago)->status_payment === 'Completado',
        ]);
    }
}
