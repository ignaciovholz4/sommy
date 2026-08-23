<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\WaConversation;
use App\Models\WaOrderDraft;

class CrearPedido
{
    public static function definition(): array
    {
        return [
            'name' => 'crear_pedido',
            'description' => 'CARGA EL PEDIDO REAL en el sistema. Usala SOLO cuando: (1) le mandaste al cliente el resumen COMPLETO (cada producto con su medida, cantidad y precio, el total, y los datos de entrega) y (2) el cliente CONFIRMÓ explícitamente ese resumen. Necesitás todos los datos de entrega: nombre completo, calle y número, localidad y provincia.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'nombre_cliente' => ['type' => 'string', 'description' => 'Nombre y apellido de quien recibe'],
                    'direccion' => ['type' => 'string', 'description' => 'Dirección de entrega (calle y número, piso/depto si aplica)'],
                    'localidad' => ['type' => 'string', 'description' => 'Localidad/ciudad de entrega (obligatoria para armar la hoja de ruta del fletero)'],
                    'provincia' => ['type' => 'string'],
                    'cp' => ['type' => 'string', 'description' => 'Código postal'],
                    'telefono_contacto' => ['type' => 'string', 'description' => 'Teléfono alternativo de contacto si dio uno distinto al del chat'],
                    'notas' => ['type' => 'string', 'description' => 'Aclaraciones del cliente (horarios, piso, forma de pago conversada, etc.)'],
                ],
                'required' => ['nombre_cliente', 'direccion', 'localidad', 'provincia'],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $draft = WaOrderDraft::where('conversation_id', $conversation->id)
            ->where('status', 'borrador')
            ->latest('id')
            ->first();

        if (!$draft || empty($draft->items)) {
            return ['error' => 'No hay una cotización vigente. Primero armá una con la herramienta cotizar.'];
        }

        // Candado: ningún producto con variantes puede ir al pedido sin su medida elegida
        foreach ($draft->items as $item) {
            if (empty($item['combinacion_id'])) {
                $tieneVariantes = \Illuminate\Support\Facades\DB::table('producto_combinaciones')
                    ->where('producto_id', $item['producto_id'])->exists();
                if ($tieneVariantes) {
                    return ['error' => 'El producto "' . ($item['descripcion'] ?? $item['producto_id']) . '" tiene variantes y la cotización no tiene la medida elegida. Preguntale al cliente la medida (mostrale las variantes reales con su precio) y volvé a cotizar con el combinacion_id.'];
                }
            }
        }

        $draft->update([
            'datos_entrega' => [
                'nombre_cliente' => $args['nombre_cliente'] ?? null,
                'direccion' => $args['direccion'] ?? '',
                'localidad' => $args['localidad'] ?? null,
                'provincia' => $args['provincia'] ?? null,
                'cp' => $args['cp'] ?? null,
                'telefono_contacto' => $args['telefono_contacto'] ?? null,
            ],
            'notas' => $args['notas'] ?? null,
        ]);

        // El cliente ya confirmó el resumen: el pedido se carga directo al
        // sistema (aparece en Pedidos, listo para cobrar y asignar flete).
        try {
            $order = app(\App\Services\OrderDraftService::class)->confirm($draft->fresh(), null);
        } catch (\Throwable $e) {
            return ['error' => 'No se pudo cargar el pedido: ' . $e->getMessage()];
        }

        return [
            'resultado' => 'ok',
            'pedido_numero' => $order->order_id,
            'total' => $draft->total,
            'nota' => 'El pedido #' . $order->order_id . ' ya quedó cargado y el sistema le avisó al cliente. No repitas la confirmación: seguí con la derivación a un vendedor para el cobro. No prometas fecha de entrega exacta.',
        ];
    }
}
