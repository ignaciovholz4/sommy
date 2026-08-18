<?php

namespace App\Services;

use App\Models\ecommerce\order_ecommerce;
use App\Models\ecommerce\payment_ecommerce;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public function __construct()
    {
        $token = config('services.mercadopago.access_token');
        if ($token) {
            MercadoPagoConfig::setAccessToken($token);
        }
    }

    public function habilitado(): bool
    {
        return !empty(config('services.mercadopago.access_token'));
    }

    /**
     * Crea (o reusa) la preferencia de Checkout Pro para un pedido.
     * Devuelve el init_point o null si falla.
     */
    public function crearPreferencia(order_ecommerce $order): ?string
    {
        if (!$this->habilitado()) {
            return null;
        }

        try {
            $items = [];
            foreach ($order->detalles()->with('producto')->get() as $detalle) {
                $items[] = [
                    'title'       => optional($detalle->producto)->nombre ?? 'Producto',
                    'quantity'    => (int) $detalle->quantity,
                    'unit_price'  => (float) $detalle->price,
                    'currency_id' => 'ARS',
                ];
            }

            // Envío como ítem para que el total de MP coincida con el del pedido
            if ($order->costo_envio > 0) {
                $items[] = [
                    'title'       => 'Costo de envío',
                    'quantity'    => 1,
                    'unit_price'  => (float) $order->costo_envio,
                    'currency_id' => 'ARS',
                ];
            }

            $client = new PreferenceClient();
            $preference = $client->create([
                'items'              => $items,
                'external_reference' => (string) $order->order_id,
                'back_urls'          => [
                    'success' => url('/pedido/gracias/' . $order->order_id),
                    'pending' => url('/pedido/gracias/' . $order->order_id),
                    'failure' => url('/pedido/gracias/' . $order->order_id),
                ],
                'notification_url'   => url('/mercadopago/webhook'),
                'statement_descriptor' => config('app.name'),
            ]);

            payment_ecommerce::where('order_id', $order->order_id)
                ->update(['mp_preference_id' => $preference->id]);

            return $preference->init_point;
        } catch (\Throwable $th) {
            Log::error('MercadoPago crearPreferencia: ' . $th->getMessage());
            return null;
        }
    }

    /**
     * Procesa una notificación de pago. SIEMPRE reconsulta el pago a la API
     * (no se confía en el payload del webhook).
     * Devuelve true si el pago quedó aprobado.
     */
    public function procesarPago(string $paymentId): bool
    {
        if (!$this->habilitado()) {
            return false;
        }

        try {
            $client = new PaymentClient();
            $payment = $client->get((int) $paymentId);

            if (!$payment || empty($payment->external_reference)) {
                return false;
            }

            $orderId = (int) $payment->external_reference;
            $registro = payment_ecommerce::where('order_id', $orderId)->first();

            if (!$registro) {
                Log::warning("MercadoPago: pago {$paymentId} sin pedido asociado (ref {$payment->external_reference})");
                return false;
            }

            $registro->mp_payment_id = (string) $payment->id;
            $registro->mp_status = $payment->status;

            if ($payment->status === 'approved') {
                $registro->status_payment = 'Completado';
                $registro->paid_at = now();
            }

            $registro->save();

            return $payment->status === 'approved';
        } catch (\Throwable $th) {
            Log::error('MercadoPago procesarPago: ' . $th->getMessage());
            return false;
        }
    }

    /**
     * Consulta manual del estado de pago de un pedido (botón "verificar pago"
     * en la página de gracias — útil cuando el webhook no llega, ej. localhost).
     */
    public function verificarPagoDePedido(order_ecommerce $order): ?string
    {
        if (!$this->habilitado()) {
            return null;
        }

        try {
            $client = new PaymentClient();
            $resultados = $client->search(new \MercadoPago\Net\MPSearchRequest(10, 0, [
                'external_reference' => (string) $order->order_id,
                'sort' => 'date_created',
                'criteria' => 'desc',
            ]));

            foreach ($resultados->results ?? [] as $pago) {
                if ($pago->status === 'approved') {
                    $this->procesarPago((string) $pago->id);
                    return 'approved';
                }
            }

            return count($resultados->results ?? []) ? $resultados->results[0]->status : null;
        } catch (\Throwable $th) {
            Log::error('MercadoPago verificarPagoDePedido: ' . $th->getMessage());
            return null;
        }
    }
}
