<?php

namespace App\Services\Ai\Tools;

use App\Models\AiAgent;
use App\Models\ArticuloConocimiento;
use App\Models\WaConversation;
use App\Models\Articulo;
use App\Models\ProductoImagen;
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
            'description' => 'Trae la información interna completa de un producto: características, preguntas frecuentes y material multimedia. Usala cuando el cliente pida detalles o tenga dudas específicas de un producto que ya encontraste con buscar_productos. Es información para VOS: contásela al cliente como un vendedor (nunca le ofrezcas "la ficha" ni hables de "cuidados": contale los beneficios).',
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

        $textos = $items->filter(fn ($i) => $i->esTexto())->map(fn ($i) => [
            'tipo'      => ArticuloConocimiento::TIPOS[$i->tipo] ?? $i->tipo,
            'titulo'    => $i->titulo,
            'contenido' => Str::limit((string) $i->contenido, 1200),
        ])->values();

        // Material multimedia: el bot lo manda como adjunto real con enviar_material
        $archivos = $items->filter(fn ($i) => !$i->esTexto() && $i->archivo)->map(fn ($i) => [
            'material_id' => (string) $i->id,
            'tipo'   => ArticuloConocimiento::TIPOS[$i->tipo] ?? $i->tipo,
            'titulo' => $i->titulo,
        ])->values();

        // Fotos reales del catálogo de la tienda (además de lo cargado en Conocimiento)
        $fotosCatalogo = ProductoImagen::where('producto_id', $id)
            ->orderByDesc('principal')->orderBy('orden')
            ->limit(4)->get()
            ->map(fn ($img) => [
                'material_id' => 'img:' . $img->id,
                'tipo'   => 'Foto',
                'titulo' => 'Foto del producto',
            ])->values();
        $archivos = $archivos->concat($fotosCatalogo)->values();

        if ($textos->isEmpty() && $archivos->isEmpty()) {
            return ['resultado' => 'Este producto no tiene ficha interna cargada. Respondé solo con la descripción del catálogo, sin inventar detalles.'];
        }

        return [
            'producto'     => $articulo->nombre,
            'conocimiento' => $textos,
            'material'     => $archivos,
            'nota'         => 'Esta información es la ficha oficial interna: usala como fuente de verdad. Para mostrarle al cliente una foto/video/audio del material, usá la herramienta enviar_material con el material_id: le llega como adjunto de WhatsApp.',
        ];
    }
}
