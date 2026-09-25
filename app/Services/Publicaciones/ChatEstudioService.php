<?php

namespace App\Services\Publicaciones;

use App\Services\Ai\OpenAiClient;
use Illuminate\Support\Facades\DB;

/**
 * Chat del Estudio de Publicaciones: el usuario pide en lenguaje natural qué
 * contenido quiere ("una imagen más cálida de noche, sin precio", "un caption
 * más corto y con humor") y el asistente llama a las mismas herramientas que
 * ya usaba la interfaz de botones (generar imágenes / generar copy).
 *
 * Regla de diseño clave: lo que escribe el usuario es SIEMPRE una indicación
 * que se SUMA al estilo de marca fijo (Mi marca) y a la fidelidad del
 * producto real — nunca lo reemplaza. Así el contenido es flexible en el
 * pedido puntual pero estático/homogéneo en la base de marca.
 */
class ChatEstudioService
{
    protected const MAX_ITERACIONES = 3;

    public function __construct(
        protected OpenAiClient $client,
        protected ImagenIaService $imagenIa,
        protected CopyGeneratorService $copys,
    ) {
    }

    /**
     * @param array $producto ficha ya mapeada (mapProducto/mapCombo)
     * @param string $rutaFoto ruta absoluta en el servidor a la foto real del producto (no viaja al navegador)
     * @param array<int, array{role:string, content:string}> $historial turnos previos (solo texto, sin tool calls)
     * @return array{reply:string, imagenes?:array, textos?:array}
     */
    public function responder(array $producto, string $rutaFoto, bool $esCombo, string $formato, bool $conPrecio, string $mensaje, array $historial): array
    {
        $tools = $this->herramientas();
        $messages = $this->armarMensajes($producto, $historial, $mensaje);
        $system = $this->armarSystem($producto, $esCombo, $formato, $conPrecio);
        $model = config('services.publicaciones.copy_model', 'gpt-4o-mini');

        $imagenesGeneradas = null;
        $textosGenerados = null;

        for ($i = 0; $i < self::MAX_ITERACIONES; $i++) {
            $response = $this->client->chat($system, $messages, $tools, $model, 0.5);

            if (!$response->hasToolCalls()) {
                $texto = trim((string) $response->text) ?: 'Listo.';
                $out = ['reply' => $texto];
                if ($imagenesGeneradas !== null) {
                    $out['imagenes'] = $imagenesGeneradas;
                }
                if ($textosGenerados !== null) {
                    $out['textos'] = $textosGenerados;
                }
                return $out;
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $response->text,
                'tool_calls' => $response->toolCalls,
            ];

            foreach ($response->toolCalls as $toolCall) {
                $args = $toolCall['arguments'];

                try {
                    if ($toolCall['name'] === 'generar_imagenes') {
                        $extraEscena = $esCombo
                            ? 'Mostrar también, junto al colchón, una base sommier a tono y un par de almohadas prolijamente acomodadas.'
                            : null;
                        $resultado = $this->imagenIa->generarVariantes(
                            $rutaFoto,
                            $args['formato'] ?? $formato,
                            max(1, min(5, (int) ($args['cantidad'] ?? 5))),
                            (string) ($args['instrucciones'] ?? ''),
                            $extraEscena
                        );
                        $imagenesGeneradas = $resultado;
                        $ok = count(array_filter($resultado, fn ($r) => !isset($r['error'])));
                        $contenido = ['ok' => true, 'generadas' => $ok, 'de' => count($resultado)];
                    } elseif ($toolCall['name'] === 'generar_copy') {
                        $textos = $this->copys->generar($producto, (bool) ($args['con_precio'] ?? $conPrecio), (string) ($args['instrucciones'] ?? ''));
                        $textosGenerados = $textos;
                        $contenido = ['ok' => true, 'listo' => true];
                    } else {
                        $contenido = ['error' => 'Herramienta desconocida'];
                    }
                } catch (\Throwable $e) {
                    $contenido = ['error' => $e->getMessage()];
                }

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'name' => $toolCall['name'],
                    'content' => json_encode($contenido, JSON_UNESCAPED_UNICODE),
                ];
            }
        }

        $out = ['reply' => 'Ya generé lo que pediste, revisá abajo.'];
        if ($imagenesGeneradas !== null) {
            $out['imagenes'] = $imagenesGeneradas;
        }
        if ($textosGenerados !== null) {
            $out['textos'] = $textosGenerados;
        }
        return $out;
    }

    protected function herramientas(): array
    {
        return [
            [
                'name' => 'generar_imagenes',
                'description' => 'Genera variantes de la foto del producto ambientada, para que el usuario elija una. Usar cuando pida una imagen, una foto, una escena, o cambios visuales (luz, ambiente, fondo, hora del día, etc).',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'instrucciones' => ['type' => 'string', 'description' => 'En español: qué pidió el usuario para esta escena (ej: "luz cálida de atardecer", "un living en vez de dormitorio", "más minimalista"). Se suma al estilo de marca fijo, no lo reemplaza.'],
                        'formato' => ['type' => 'string', 'enum' => ['feed', 'story', 'ml'], 'description' => 'Formato pedido. Si no lo aclara, usar el que ya está seleccionado.'],
                        'cantidad' => ['type' => 'integer', 'description' => 'Cuántas variantes generar (1 a 5). Por defecto 5, salvo que el usuario pida menos.'],
                    ],
                    'required' => ['instrucciones'],
                ],
            ],
            [
                'name' => 'generar_copy',
                'description' => 'Genera título de MercadoLibre, descripción, caption de Instagram/Facebook y mensaje de WhatsApp. Usar cuando pida texto, caption, descripción, o cambios de tono/estilo de escritura.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'instrucciones' => ['type' => 'string', 'description' => 'En español: qué tono o enfoque pidió (ej: "más corto y con humor", "enfocado en el Día de la Madre").'],
                        'con_precio' => ['type' => 'boolean', 'description' => 'Si hay que mostrar el precio en los textos. Si no lo aclara, usar el valor ya seleccionado.'],
                    ],
                    'required' => ['instrucciones'],
                ],
            ],
        ];
    }

    protected function armarMensajes(array $producto, array $historial, string $mensaje): array
    {
        $messages = [];
        foreach ($historial as $h) {
            if (in_array($h['role'] ?? '', ['user', 'assistant'], true) && trim((string) ($h['content'] ?? '')) !== '') {
                $messages[] = ['role' => $h['role'], 'content' => (string) $h['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $mensaje];
        return $messages;
    }

    protected function armarSystem(array $producto, bool $esCombo, string $formato, bool $conPrecio): string
    {
        $contextos = DB::table('publicaciones_recursos')
            ->where('tipo', 'contexto')->where('activo', 1)
            ->get(['titulo', 'contenido']);

        $system = "Sos el asistente del Estudio de Publicaciones de Sommy (colchones). Ayudás a crear contenido "
            . "para Instagram/Facebook/MercadoLibre a partir de lo que te pide el usuario en lenguaje natural.\n\n"
            . "Producto/combo seleccionado ahora mismo:\n" . json_encode($producto, JSON_UNESCAPED_UNICODE) . "\n"
            . "Formato seleccionado: {$formato}. Mostrar precio: " . ($conPrecio ? 'sí' : 'no') . ".\n\n"
            . "REGLAS:\n"
            . "- El estilo visual de marca (ambientación, luz, paleta) y la fidelidad del colchón real a la foto se aplican SIEMPRE automáticamente, no dependen de vos ni hace falta que los menciones.\n"
            . "- Nunca inventes especificaciones (precio, medidas, materiales) que no estén en la ficha del producto de arriba.\n"
            . "- Si el usuario pide una imagen/foto/escena o cambios visuales: llamá a generar_imagenes, traduciendo su pedido a 'instrucciones' claras en español.\n"
            . "- Si pide texto/caption/descripción o un cambio de tono: llamá a generar_copy.\n"
            . "- Si pide ambas cosas, podés llamar a las dos herramientas en el mismo turno.\n"
            . "- Si el pedido es ambiguo (ej: no se entiende qué quiere), preguntá antes de llamar una herramienta.\n"
            . "- IMPORTANTE: cuando una herramienta ya se ejecutó, el resultado (imágenes o textos) se muestra aparte, abajo, en la interfaz. Tu respuesta final en ese caso tiene que ser SOLO una confirmación breve (una frase, sin emojis de sobra), nunca repetir ni reescribir el contenido generado. Ej: 'Listo, generé 5 opciones para que elijas 👇' o 'Ya está el caption, lo tenés abajo para revisar.'\n"
            . "- Respuestas cortas, en español rioplatense, tono cercano y directo, sin tecnicismos. No repitas la ficha del producto ni links.";

        if ($contextos->isNotEmpty()) {
            $system .= "\n\nCONTEXTO DEL NEGOCIO (datos reales, usalos si son relevantes):\n"
                . $contextos->map(fn ($c) => "- {$c->titulo}: {$c->contenido}")->implode("\n");
        }

        return $system;
    }
}
