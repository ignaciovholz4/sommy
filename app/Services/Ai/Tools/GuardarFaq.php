<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\Articulo;
use App\Models\ArticuloConocimiento;
use App\Models\WaConversation;

/**
 * El bot va construyendo la base de conocimiento solo: cuando responde una
 * pregunta de producto que la ficha no cubria, la guarda como FAQ en el
 * Conocimiento del articulo (aparece en la pantalla de Conocimiento, donde
 * el dueño puede editarla o borrarla).
 */
class GuardarFaq
{
    public static function definition(): array
    {
        return [
            'name' => 'guardar_faq',
            'description' => 'Guarda una pregunta frecuente con su respuesta en la ficha de conocimiento del producto, para que quede configurada para futuras consultas. Usala cuando un cliente haga una pregunta útil sobre un producto y la respuesta NO estuviera ya en la ficha (info_producto). No la uses para datos que ya están cargados ni para charla que no sea del producto.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'producto_id' => [
                        'type' => 'integer',
                        'description' => 'El producto_id del artículo al que corresponde la pregunta',
                    ],
                    'pregunta' => [
                        'type' => 'string',
                        'description' => 'La pregunta del cliente, redactada de forma general (ej: "¿Sirve para una persona de 100 kg?")',
                    ],
                    'respuesta' => [
                        'type' => 'string',
                        'description' => 'La respuesta correcta y verificada que se le dio',
                    ],
                ],
                'required' => ['producto_id', 'pregunta', 'respuesta'],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $articulo = Articulo::find((int) ($args['producto_id'] ?? 0));
        if (!$articulo) {
            return ['error' => 'No existe un producto con ese id.'];
        }

        $pregunta = mb_substr(trim((string) ($args['pregunta'] ?? '')), 0, 140);
        $respuesta = trim((string) ($args['respuesta'] ?? ''));
        if ($pregunta === '' || $respuesta === '') {
            return ['error' => 'Faltan la pregunta o la respuesta.'];
        }

        // Evitar duplicados: si ya hay una FAQ muy parecida, no se vuelve a guardar
        $existe = ArticuloConocimiento::where('articulo_id', $articulo->idarticulo)
            ->where('tipo', 'faq')
            ->where('titulo', 'like', '%' . mb_substr($pregunta, 0, 40) . '%')
            ->exists();
        if ($existe) {
            return ['resultado' => 'Ya existe una FAQ similar para este producto, no se duplicó.'];
        }

        ArticuloConocimiento::create([
            'articulo_id' => $articulo->idarticulo,
            'tipo'        => 'faq',
            'titulo'      => $pregunta,
            'contenido'   => $respuesta . "\n\n(FAQ aprendida por el bot en una conversación real)",
            'activo'      => true,
        ]);

        return ['resultado' => 'FAQ guardada en la ficha de ' . $articulo->nombre . '. Seguí la conversación normalmente.'];
    }
}
