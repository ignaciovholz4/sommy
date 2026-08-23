<?php

namespace App\Services;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Cliente;
use App\Models\WaOrderDraft;
use App\Models\ecommerce\order_detail_ecommerce;
use App\Models\ecommerce\order_ecommerce;
use App\Support\PhoneAr;
use Illuminate\Support\Facades\DB;

/**
 * Convierte un borrador de pedido armado por el agente IA (wa_order_drafts)
 * en un pedido real de order_ecommerce con origen "whatsapp".
 * Espeja la logica de carga manual de OrderController@storeManual.
 */
class OrderDraftService
{
    /**
     * @throws \RuntimeException si el draft no esta en estado confirmable
     */
    public function confirm(WaOrderDraft $draft, int $confirmedByUserId): order_ecommerce
    {
        if (!in_array($draft->status, ['borrador', 'pendiente_confirmacion'], true)) {
            throw new \RuntimeException('El borrador no está en un estado confirmable (' . $draft->status . ').');
        }
        if (empty($draft->items)) {
            throw new \RuntimeException('El borrador no tiene productos.');
        }

        return DB::transaction(function () use ($draft, $confirmedByUserId) {
            $conversation = $draft->conversation;

            // Cliente: el vinculado al draft, o el de la conversacion, o crear uno con los datos del chat
            $entregaCliente = $draft->datos_entrega ?? [];
            $cliente = $draft->cliente ?: $conversation->cliente;
            if (!$cliente) {
                $cliente = Cliente::create([
                    'nombre' => $entregaCliente['nombre_cliente'] ?? ($conversation->profile_name ?: ('Cliente ' . ucfirst($conversation->channel))),
                    'telefono' => $entregaCliente['telefono_contacto'] ?? (PhoneAr::pretty($conversation->phone_e164) ?? ''),
                    'email' => '',
                    'direccion' => $entregaCliente['direccion'] ?? '',
                    'localidad' => $entregaCliente['localidad'] ?? null,
                    'provincia' => $entregaCliente['provincia'] ?? null,
                    'codigo_postal' => $entregaCliente['cp'] ?? null,
                    'estatus' => 1,
                ]);
                $conversation->update(['cliente_id' => $cliente->idcliente]);
            } else {
                // Completar datos que le falten al cliente con lo juntado en el chat
                $cliente->fill(array_filter([
                    'direccion' => $cliente->direccion ?: ($entregaCliente['direccion'] ?? null),
                    'localidad' => $cliente->localidad ?: ($entregaCliente['localidad'] ?? null),
                    'provincia' => $cliente->provincia ?: ($entregaCliente['provincia'] ?? null),
                    'codigo_postal' => $cliente->codigo_postal ?: ($entregaCliente['cp'] ?? null),
                ]))->save();
            }

            $draft->recalcularTotales();

            $entrega = $draft->datos_entrega ?? [];

            $order = order_ecommerce::create([
                'status_order_id' => 1, // Pendiente
                'cliente_id' => $cliente->idcliente,
                'origen' => 'whatsapp',
                'subtotal_amount' => $draft->subtotal,
                'total_amount' => $draft->total,
                'order_date' => now(),
                'costo_envio' => $draft->costo_envio,
                'descuento_pago' => 0,
                'direccion_localidad' => $entrega['localidad'] ?? null,
                'direccion_provincia' => $entrega['provincia'] ?? null,
                'direccion_cp' => $entrega['cp'] ?? null,
                'additional_info' => trim('Pedido tomado por WhatsApp'
                    . ($draft->ai_agent_id ? ' (agente IA)' : '')
                    . (!empty($entrega['direccion']) ? '. Entrega: ' . $entrega['direccion'] : '')
                    . (!empty($entrega['nombre_cliente']) ? '. Recibe: ' . $entrega['nombre_cliente'] : '')
                    . (!empty($entrega['telefono_contacto']) ? ' (tel ' . $entrega['telefono_contacto'] . ')' : '')
                    . ($draft->notas ? '. Notas: ' . $draft->notas : '')),
            ]);

            foreach ($draft->items as $item) {
                order_detail_ecommerce::create([
                    'order_ecommerce_id' => $order->order_id,
                    'product_id' => $item['producto_id'],
                    'producto_variacion_variante_id' => $item['combinacion_id'] ?? null,
                    'quantity' => $item['cantidad'],
                    'price' => $item['precio_unitario'],
                    'total' => $item['cantidad'] * $item['precio_unitario'],
                ]);
            }

            $draft->update([
                'status' => 'confirmado',
                'cliente_id' => $cliente->idcliente,
                'confirmed_by_user_id' => $confirmedByUserId,
                'order_ecommerce_id' => $order->order_id,
                'subtotal' => $draft->subtotal,
                'total' => $draft->total,
            ]);

            // Avisar al cliente por WhatsApp que su pedido quedo confirmado
            $message = $conversation->messages()->create([
                'direction' => 'out',
                'type' => 'text',
                'body' => "✅ ¡Tu pedido #{$order->order_id} quedó confirmado! Total: $" . number_format($draft->total, 2, ',', '.')
                    . ". En breve nos comunicamos para coordinar la entrega. ¡Gracias!",
                'status' => 'pending',
                'sent_by_user_id' => $confirmedByUserId,
            ]);
            SendWhatsAppMessage::dispatch($message->id);

            return $order;
        });
    }

    public function reject(WaOrderDraft $draft, int $userId, ?string $motivo = null): void
    {
        $draft->update(['status' => 'rechazado', 'confirmed_by_user_id' => $userId]);

        if ($motivo) {
            $draft->conversation->messages()->create([
                'direction' => 'out',
                'type' => 'text',
                'body' => 'Borrador rechazado: ' . $motivo,
                'is_internal_note' => true,
                'sent_by_user_id' => $userId,
            ]);
        }
    }
}
