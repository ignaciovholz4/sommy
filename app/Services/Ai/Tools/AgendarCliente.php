<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\Cliente;
use App\Models\WaConversation;
use App\Support\PhoneAr;

/**
 * El bot agenda al cliente en la base a medida que conversa: crea la ficha
 * con el telefono del chat y la va completando con cada dato nuevo (nombre,
 * localidad, direccion...). La conversacion queda vinculada al cliente, y
 * cuando se arma un pedido la ficha ya esta lista.
 */
class AgendarCliente
{
    public static function definition(): array
    {
        return [
            'name' => 'agendar_cliente',
            'description' => 'Registra o actualiza la ficha del cliente en la base de datos con los datos que te va contando (nombre, localidad, dirección, email, DNI/CUIT). Usala apenas te diga su nombre, y de nuevo cada vez que sume un dato. Es silenciosa: no le avises al cliente que lo estás agendando.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'nombre'    => ['type' => 'string', 'description' => 'Nombre (y apellido si lo dio)'],
                    'localidad' => ['type' => 'string', 'description' => 'Localidad/ciudad de donde escribe'],
                    'provincia' => ['type' => 'string'],
                    'direccion' => ['type' => 'string', 'description' => 'Dirección (calle y número) si la dio'],
                    'codigo_postal' => ['type' => 'string'],
                    'email'     => ['type' => 'string'],
                    'dni_cuit'  => ['type' => 'string'],
                ],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $datos = array_filter(array_map(fn ($v) => trim((string) $v) ?: null, [
            'nombre'        => $args['nombre'] ?? null,
            'localidad'     => $args['localidad'] ?? null,
            'provincia'     => $args['provincia'] ?? null,
            'direccion'     => $args['direccion'] ?? null,
            'codigo_postal' => $args['codigo_postal'] ?? null,
            'email'         => $args['email'] ?? null,
            'dni_cuit'      => $args['dni_cuit'] ?? null,
        ]));

        if (empty($datos)) {
            return ['error' => 'No pasaste ningún dato para agendar.'];
        }

        $cliente = $conversation->cliente;

        // Sin cliente vinculado: buscar por telefono del chat o crear la ficha
        if (!$cliente && $conversation->phone_e164) {
            $cliente = Cliente::wherePhoneMatches($conversation->phone_e164)->first();
        }
        if (!$cliente) {
            $cliente = Cliente::create([
                'nombre'   => $datos['nombre'] ?? ($conversation->profile_name ?: 'Cliente WhatsApp'),
                'telefono' => $conversation->phone_e164 ? ltrim(PhoneAr::pretty($conversation->phone_e164) ?? $conversation->phone_e164, '+') : '',
                'email'    => $datos['email'] ?? '',
                'direccion'=> $datos['direccion'] ?? '',
                'estatus'  => 1,
            ]);
        }

        // Completar/actualizar: el nombre generico se pisa, el resto solo si vino dato nuevo
        $nombreGenerico = !$cliente->nombre
            || str_starts_with($cliente->nombre, 'Cliente ')
            || $cliente->nombre === $conversation->profile_name;
        foreach ($datos as $campo => $valor) {
            if ($campo === 'nombre' && !$nombreGenerico) {
                continue; // no pisar un nombre real ya cargado
            }
            $cliente->{$campo} = $valor;
        }
        $cliente->save();

        if ($conversation->cliente_id !== $cliente->idcliente) {
            $conversation->update(['cliente_id' => $cliente->idcliente]);
        }

        return [
            'resultado' => 'Cliente agendado/actualizado: ' . $cliente->nombre
                . ($cliente->localidad ? ' (' . $cliente->localidad . ')' : '')
                . '. Seguí la conversación normalmente.',
        ];
    }
}
