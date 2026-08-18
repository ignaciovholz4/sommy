<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\ArticuloConocimiento;
use App\Models\WaConversation;
use App\Models\Articulo;
use Illuminate\Support\Str;

/**
 * Ficha interna del producto para el bot de ventas: instrucciones,
 * características, FAQs y material multimedia cargados en la base de
 * conocimiento (articulo_conocimiento).
 */
class InfoProducto
{
    public static function definition(): array
    {
        return [
            'name' => 'info_producto',
            'description' => 'Trae la ficha interna completa de un producto: instrucciones de uso, características técnicas, preguntas frecuentes y material multimedia. Usala cuando el cliente pida detalles, cuidados, materiales o dudas específicas de un producto que ya encontraste con buscar_productos.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'producto_id' => [
                        'type' => 'integer',
                        'description' => 'El producto_id devuelto por buscar_productos',
                    ],
                ],
                'required' => ['producto_id'],
            ],
        ];
    }

    public function execute(array $args, AiAgent $agent, WaConversation $conversation): array
    {
        $id = (int) ($args['producto_id'] ?? 0);
        $articulo = Articulo::find($id);
        if (!$articulo) {
            return ['error' => 'No existe un producto con ese id.'];
        }

        $items = ArticuloConocimiento::where('articulo_id', $id)
            ->where('activo', true)
            ->orderBy('tipo')
            ->get();

        if ($items->isEmpty()) {
            return ['resultado' => 'Este producto no tiene ficha interna cargada. Respondé solo con la descripción del catálogo, sin inventar detalles.'];
        }

        $textos = $items->filter(fn ($i) => $i->esTexto())->map(fn ($i) => [
            'tipo'      => ArticuloConocimiento::TIPOS[$i->tipo] ?? $i->tipo,
            'titulo'    => $i->titulo,
            'contenido' => Str::limit((string) $i->contenido, 1200),
        ])->values();

        // Material multimedia: el bot puede ofrecer el link al cliente
        $archivos = $items->filter(fn ($i) => !$i->esTexto() && $i->archivo)->map(fn ($i) => [
            'tipo'   => ArticuloConocimiento::TIPOS[$i->tipo] ?? $i->tipo,
            'titulo' => $i->titulo,
            'link'   => $i->archivo_url,
        ])->values();

        return [
            'producto'     => $articulo->nombre,
            'conocimiento' => $textos,
            'material'     => $archivos,
            'nota'         => 'Esta información es la ficha oficial interna: usala como fuente de verdad. Si el cliente quiere ver un video/imagen/audio del material, compartile el link.',
        ];
    }
}
