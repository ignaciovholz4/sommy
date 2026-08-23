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
            'description' => 'Pasa la cotización vigente a "pendiente de confirmación" para que un vendedor humano la revise y confirme el pedido real. Usala recién cuando el cliente aceptó la cotización y te dio TODOS los datos de entrega: nombre completo, dirección con calle y número, localidad y provincia (el flete se organiza con esos datos). NO crea el pedido definitivo: eso lo hace un vendedor.',
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

        $draft->update([
            'status' => 'pendiente_confirmacion',
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

        return [
            'resultado' => 'ok',
            'pedido_borrador_id' => $draft->id,
            'total' => $draft->total,
            'nota' => 'Avisale al cliente que su pedido quedó registrado y que un asesor se lo confirma a la brevedad. No prometas fecha de entrega exacta.',
        ];
    }
}
