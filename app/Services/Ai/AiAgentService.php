<?php

namespace App\Services\Ai;

use App\Jobs\SendWhatsAppMessage;
use App\Models\AiAgent;
use App\Models\AiAgentRun;
use App\Models\WaConversation;
use App\Models\WaMessage;
use App\Services\Ai\Contracts\LlmClient;
use App\Services\Ai\Tools\BuscarProductos;
use App\Services\Ai\Tools\ConsultarStock;
use App\Services\Ai\Tools\Cotizar;
use App\Services\Ai\Tools\CrearPedido;
use App\Services\Ai\Tools\DerivarAHumano;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Loop del agente de venta IA: recibe un mensaje entrante de WhatsApp,
 * arma el contexto de la conversacion, llama al LLM con herramientas
 * y responde (o deriva a un humano).
 */
class AiAgentService
{
    protected const MAX_ITERATIONS = 6;

    /** Costo estimado USD por millon de tokens [input, output] por prefijo de modelo */
    protected const PRICING = [
        'gpt-4o-mini' => [0.15, 0.60],
        'gpt-4o' => [2.50, 10.00],
        'claude-haiku' => [1.00, 5.00],
        'claude-sonnet' => [3.00, 15.00],
        'claude' => [3.00, 15.00],
    ];

    public function handle(WaMessage $inbound): void
    {
        $conversation = $inbound->conversation;
        $agent = $conversation->aiAgent;

        // Debounce: si el cliente mando varios mensajes seguidos, responde solo el ultimo
        $newer = $conversation->messages()
            ->where('direction', 'in')
            ->where('id', '>', $inbound->id)
            ->exists();
        if ($newer) {
            return;
        }

        if ($conversation->mode !== 'bot' || !$agent) {
            return;
        }

        // Guardas: agente apagado, fuera de horario, tope de gasto o demasiados turnos sin humano
        if (!$agent->activo || !$agent->isWithinSchedule() || $agent->overBudget()
            || $this->botTurnsWithoutHuman($conversation) >= $agent->max_turnos_sin_humano) {
            $this->escalate($conversation, $agent, 'Guarda del sistema (agente inactivo, fuera de horario, tope de gasto o máximo de turnos alcanzado).');
            return;
        }

        // El bot toma la conversación: pasa a "en atención" automáticamente
        if (!in_array($conversation->status, ['en_atencion'])) {
            $conversation->update(['status' => 'en_atencion']);
        }

        $run = AiAgentRun::create([
            'ai_agent_id' => $agent->id,
            'conversation_id' => $conversation->id,
            'wa_message_id_in' => $inbound->id,
            'status' => 'ok',
        ]);

        try {
            $this->runLoop($conversation, $agent, $run);
        } catch (\Throwable $e) {
            Log::error('Error del agente IA', [
                'agent' => $agent->id,
                'conversation' => $conversation->id,
                'error' => $e->getMessage(),
            ]);
            $run->update(['status' => 'error', 'error' => Str::limit($e->getMessage(), 2000)]);
            $this->escalate($conversation, $agent, 'Error técnico del agente: ' . Str::limit($e->getMessage(), 200));
        }
    }

    protected function runLoop(WaConversation $conversation, AiAgent $agent, AiAgentRun $run): void
    {
        $client = $this->clientFor($agent);
        $tools = $this->toolDefinitions($agent);
        $messages = $this->buildHistory($conversation);
        $system = $this->buildSystem($conversation, $agent);

        $promptTokens = 0;
        $completionTokens = 0;
        $toolLog = [];

        $correccionesPrecio = 0;

        for ($i = 1; $i <= self::MAX_ITERATIONS; $i++) {
            $response = $client->chat($system, $messages, $tools, $agent->model, $agent->temperature);
            $promptTokens += $response->promptTokens;
            $completionTokens += $response->completionTokens;

            if (!$response->hasToolCalls()) {
                $texto = (string) $response->text;

                // GUARDA anti-precios-inventados: si la respuesta menciona plata
                // y en este turno no consultó el catálogo, se la rebota y se la
                // obliga a verificar con herramientas antes de responder.
                $mencionaPrecio = preg_match('/\$\s?[\d]{2}[\d\.\,]{2,}/u', $texto);
                $consultoCatalogo = collect($toolLog)->pluck('tool')
                    ->intersect(['buscar_productos', 'ver_catalogo', 'info_producto', 'cotizar'])->isNotEmpty();

                if ($mencionaPrecio && !$consultoCatalogo && $correccionesPrecio < 2) {
                    $correccionesPrecio++;
                    $messages[] = ['role' => 'assistant', 'content' => $texto];
                    $messages[] = ['role' => 'system', 'content' => 'CORRECCIÓN OBLIGATORIA: tu respuesta menciona precios pero NO consultaste el catálogo en este turno. Está PROHIBIDO informar montos sin verificarlos con buscar_productos/ver_catalogo. Consultá las herramientas AHORA y reescribí la respuesta solo con precios reales del sistema.'];
                    continue;
                }
                if ($mencionaPrecio && !$consultoCatalogo) {
                    // No obedeció tras 2 correcciones: mejor derivar que mentir
                    $this->escalate($conversation, $agent, 'Guarda de precios: el agente insistió en informar montos sin verificar el catálogo');
                    $run->status = 'escalated';
                    break;
                }

                if ($texto !== '') {
                    $this->reply($conversation, $agent, $texto);

                    // GUARDA de derivación real: si ANUNCIA que deriva pero no
                    // ejecutó derivar_a_humano, la derivación se hace igual.
                    $anunciaDerivacion = preg_match('/deriv|compañer|colega|un vendedor (te|se) va|te va a contactar|se va a comunicar|te lo confirma (un|el)/iu', $texto);
                    $derivoDeVerdad = collect($toolLog)->pluck('tool')->contains('derivar_a_humano');
                    if ($anunciaDerivacion && !$derivoDeVerdad) {
                        $this->escalate($conversation, $agent, 'El agente anunció derivación en su texto (guarda automática)', false);
                        $run->status = 'escalated';
                    }
                }
                break;
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $response->text,
                'tool_calls' => $response->toolCalls,
            ];

            $escalated = false;
            foreach ($response->toolCalls as $toolCall) {
                $toolLog[] = ['tool' => $toolCall['name'], 'args' => $toolCall['arguments']];

                if ($toolCall['name'] === 'derivar_a_humano') {
                    $motivo = $toolCall['arguments']['motivo'] ?? 'El agente decidió derivar';
                    if ($agent->mensaje_derivacion) {
                        $this->reply($conversation, $agent, $agent->mensaje_derivacion);
                    }
                    $this->escalate($conversation, $agent, $motivo, false);
                    $run->status = 'escalated';
                    $escalated = true;
                    break;
                }

                $result = $this->executeTool($toolCall, $agent, $conversation);
                // Registrar si la tool falló: las etiquetas automáticas solo se
                // ponen sobre acciones que realmente funcionaron
                $toolLog[count($toolLog) - 1]['error'] = isset($result['error']);
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'name' => $toolCall['name'],
                    'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            }

            if ($escalated) {
                break;
            }
        }

        $run->fill([
            'iterations' => $i ?? 0,
            'tool_calls' => $toolLog,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'costo_estimado' => $this->estimateCost($agent->model, $promptTokens, $completionTokens),
        ])->save();

        $this->autoEtiquetar($conversation, $toolLog);
    }

    /**
     * Etiquetas automaticas segun lo que hizo el bot en el turno: el equipo
     * filtra la bandeja por etapa comercial sin etiquetar a mano.
     */
    protected function autoEtiquetar(WaConversation $conversation, array $toolLog): void
    {
        $mapa = [
            'buscar_productos'  => ['consultando productos', '#6f42c1'],
            'ver_catalogo'      => ['consultando productos', '#6f42c1'],
            'info_producto'     => ['consultando productos', '#6f42c1'],
            'cotizar'           => ['cotizado', '#17a2b8'],
            'crear_pedido'      => ['pedido armado', '#28a745'],
            'derivar_a_humano'  => ['derivado', '#dc3545'],
        ];

        try {
            foreach ($toolLog as $call) {
                if (!isset($mapa[$call['tool']]) || !empty($call['error'])) {
                    continue;
                }
                [$nombre, $color] = $mapa[$call['tool']];
                $tag = \App\Models\WaTag::firstOrCreate(['nombre' => $nombre], ['color' => $color]);
                $conversation->tags()->syncWithoutDetaching([$tag->id]);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo auto-etiquetar la conversación', ['error' => $e->getMessage()]);
        }
    }

    // ------------------------------------------------------------------

    protected function clientFor(AiAgent $agent): LlmClient
    {
        return $agent->provider === 'anthropic' ? new AnthropicClient() : new OpenAiClient();
    }

    protected function toolDefinitions(AiAgent $agent): array
    {
        $all = [
            'buscar_productos' => BuscarProductos::definition(),
            'consultar_stock' => ConsultarStock::definition(),
            'cotizar' => Cotizar::definition(),
            'crear_pedido' => CrearPedido::definition(),
            'derivar_a_humano' => DerivarAHumano::definition(),
        ];

        $defs = array_values(array_intersect_key($all, array_flip($agent->tools_enabled ?? [])));

        // Ficha interna, envío de material, catálogo y FAQs: acompañan a buscar_productos
        if ($agent->toolEnabled('buscar_productos')) {
            $defs[] = Tools\InfoProducto::definition();
            $defs[] = Tools\EnviarMaterial::definition();
            $defs[] = Tools\VerCatalogo::definition();
            $defs[] = Tools\GuardarFaq::definition();
        }

        // Herramientas de gestión de estado: siempre disponibles para todos los agentes
        $defs[] = Tools\CerrarConversacion::definition();
        $defs[] = Tools\EtiquetarConversacion::definition();
        $defs[] = Tools\AgendarCliente::definition();
        $defs[] = Tools\ConsultarEnvio::definition();

        return $defs;
    }

    protected function executeTool(array $toolCall, AiAgent $agent, WaConversation $conversation): array
    {
        try {
            $tool = match ($toolCall['name']) {
                'buscar_productos' => new BuscarProductos(),
                'consultar_stock' => new ConsultarStock(),
                'cotizar' => new Cotizar(),
                'crear_pedido' => new CrearPedido(),
                'info_producto' => new Tools\InfoProducto(),
                'enviar_material' => new Tools\EnviarMaterial(),
                'ver_catalogo' => new Tools\VerCatalogo(),
                'guardar_faq' => new Tools\GuardarFaq(),
                'cerrar_conversacion' => new Tools\CerrarConversacion(),
                'etiquetar_conversacion' => new Tools\EtiquetarConversacion(),
                'agendar_cliente' => new Tools\AgendarCliente(),
                'consultar_envio' => new Tools\ConsultarEnvio(),
                default => null,
            };

            // Tools acompañantes que no figuran en tools_enabled del agente
            $esAcompanante = in_array($toolCall['name'], ['cerrar_conversacion', 'etiquetar_conversacion', 'agendar_cliente', 'consultar_envio'])
                || (in_array($toolCall['name'], ['info_producto', 'enviar_material', 'ver_catalogo', 'guardar_faq']) && $agent->toolEnabled('buscar_productos'));

            if (!$tool || (!$agent->toolEnabled($toolCall['name']) && !$esAcompanante)) {
                return ['error' => 'Herramienta no disponible'];
            }

            return $tool->execute($toolCall['arguments'], $agent, $conversation);
        } catch (\Throwable $e) {
            Log::warning('Error ejecutando tool ' . $toolCall['name'], ['error' => $e->getMessage()]);
            return ['error' => 'Fallo interno de la herramienta'];
        }
    }

    /**
     * Historial de la conversacion en formato neutral (ultimos 20 mensajes visibles).
     */
    protected function buildHistory(WaConversation $conversation): array
    {
        $recent = $conversation->messages()
            ->where('is_internal_note', false)
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->reverse();

        // Vision: las 2 imagenes mas recientes del cliente se mandan al modelo
        // para que las VEA (identificar el producto de una foto, el sommier, etc.)
        $imagenesConVision = $recent
            ->filter(fn ($m) => $m->direction === 'in' && $m->type === 'image' && $m->media_path)
            ->sortByDesc('id')->take(2)->pluck('id')->all();

        $messages = [];
        foreach ($recent as $m) {
            $body = trim((string) $m->body);

            if (in_array($m->id, $imagenesConVision, true)) {
                $ruta = storage_path('app/' . $m->media_path);
                if (file_exists($ruta) && filesize($ruta) < 8_000_000) {
                    $messages[] = [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $body !== '' ? $body : 'El cliente mandó esta imagen:'],
                            ['type' => 'image_url', 'image_url' => [
                                'url' => 'data:' . ($m->media_mime ?: 'image/jpeg') . ';base64,' . base64_encode(file_get_contents($ruta)),
                            ]],
                        ],
                    ];
                    continue;
                }
            }

            if ($body === '') {
                $body = '[' . $m->type . ' adjunto]';
            }
            $messages[] = [
                'role' => $m->direction === 'in' ? 'user' : 'assistant',
                'content' => $body,
            ];
        }

        // El primer mensaje debe ser del usuario (requisito Anthropic y buena praxis)
        while ($messages && $messages[0]['role'] !== 'user') {
            array_shift($messages);
        }

        return $messages;
    }

    protected function buildSystem(WaConversation $conversation, AiAgent $agent): string
    {
        $system = $agent->system_prompt;

        $cliente = $conversation->cliente;
        if ($cliente) {
            $system .= "\n\nDatos del cliente (del sistema): nombre " . trim($cliente->nombre . ' ' . ($cliente->paterno ?? ''))
                . ($cliente->localidad ? ", localidad {$cliente->localidad}" : '')
                . ($cliente->direccion ? ", dirección registrada: {$cliente->direccion}" : '') . '.';

            // Historial de compras: para reconocerlo como cliente y asesorar mejor.
            // OJO: es contexto tuyo — el ESTADO de pedidos en curso NUNCA se informa.
            $compras = collect();
            $ventas = \Illuminate\Support\Facades\DB::table('ventas')
                ->join('detalle_ventas as dv', 'dv.venta_id', '=', 'ventas.idventa')
                ->join('productos as p', 'p.idarticulo', '=', 'dv.articulo_id')
                ->where('ventas.cliente_id', $cliente->idcliente)
                ->where('ventas.estado', '!=', 'anulada')
                ->orderByDesc('ventas.fecha')->limit(5)
                ->get(['p.nombre', 'ventas.fecha']);
            $pedidos = \Illuminate\Support\Facades\DB::table('order_ecommerce as o')
                ->join('order_detail_ecommerce as od', 'od.order_ecommerce_id', '=', 'o.order_id')
                ->join('productos as p', 'p.idarticulo', '=', 'od.product_id')
                ->where('o.cliente_id', $cliente->idcliente)
                ->orderByDesc('o.order_date')->limit(5)
                ->get(['p.nombre', 'o.order_date as fecha']);
            $compras = $ventas->concat($pedidos)->sortByDesc('fecha')->take(5);

            if ($compras->isNotEmpty()) {
                $lineas = $compras->map(fn ($c) => $c->nombre . ' (' . \Carbon\Carbon::parse($c->fecha)->format('m/Y') . ')')->unique()->implode('; ');
                $system .= "\nEs un cliente que YA NOS COMPRÓ. Últimas compras: {$lineas}. "
                    . 'Saludalo como cliente conocido y, si viene al caso, preguntale cómo le fue con lo que compró.';
            }
        } elseif ($conversation->profile_name) {
            $system .= "\n\nEl cliente aparece en WhatsApp como \"{$conversation->profile_name}\" (todavía no está registrado en el sistema).";
        }

        $draft = $conversation->orderDrafts()
            ->whereIn('status', ['borrador', 'pendiente_confirmacion'])
            ->latest('id')->first();
        if ($draft) {
            $detalle = collect($draft->items ?? [])
                ->map(fn ($i) => "{$i['cantidad']}x {$i['descripcion']} ($" . number_format($i['precio_unitario'], 2, ',', '.') . ')')
                ->implode('; ');
            $estado = $draft->status === 'borrador' ? 'cotización en curso' : 'pedido pendiente de confirmación por un vendedor';
            $system .= "\n\nEstado actual: hay una {$estado} en esta conversación: {$detalle}. Total $" . number_format($draft->total, 2, ',', '.') . '.';
        }

        if ($conversation->messages()->where('direction', 'in')->count() <= 1) {
            $system .= "\n\nEste es el PRIMER mensaje de este cliente en la conversación: abrí tu respuesta con un saludo de bienvenida cálido y natural (ej: \"Hola, ¿cómo andás? Bienvenido/a a Sommy Argentina\") antes de responder su consulta.";
        }

        $system .= "\n\nGestión de la conversación: cuando la consulta quede resuelta y el cliente se despida o no necesite nada más, usá la herramienta cerrar_conversacion (podés despedirte en el mismo turno). No la uses si quedó algo pendiente.";

        $system .= "\n\nFecha y hora actual: " . now()->format('d/m/Y H:i') . ' (Argentina).';

        return $system;
    }

    /**
     * Respuestas seguidas del bot desde la ultima intervencion humana
     * (mensaje entrante del cliente no corta la racha; un humano si).
     */
    protected function botTurnsWithoutHuman(WaConversation $conversation): int
    {
        // Se cuentan CORRIDAS del agente (una por mensaje entrante), no mensajes
        // enviados: una respuesta dividida en varios mensajes sigue siendo 1 turno.
        $lastHumanAt = $conversation->messages()
            ->whereNotNull('sent_by_user_id')
            ->max('created_at');

        return \App\Models\AiAgentRun::where('conversation_id', $conversation->id)
            ->when($lastHumanAt, fn ($q) => $q->where('created_at', '>', $lastHumanAt))
            ->count();
    }

    protected function reply(WaConversation $conversation, AiAgent $agent, string $text): void
    {
        // WhatsApp usa *negrita* con un solo asterisco, no **markdown**
        $text = preg_replace('/\*\*(.+?)\*\*/s', '*$1*', $text);

        // Como una persona real: cada bloque separado por línea en blanco sale
        // como un mensaje aparte (la cola los procesa en orden, y el bridge
        // simula "escribiendo..." entre uno y otro). Los separadores decorativos
        // del modelo ("---", "***") no son mensajes: se descartan.
        $chunks = array_values(array_filter(
            array_map('trim', preg_split("/\n{2,}/", trim($text))),
            fn ($c) => $c !== '' && !preg_match('/^[\s\-_=*·—#]+$/u', $c)
        ));
        $chunks = array_slice($chunks, 0, 5) ?: [trim($text)];

        foreach ($chunks as $chunk) {
            $message = $conversation->messages()->create([
                'direction' => 'out',
                'type' => 'text',
                'body' => $chunk,
                'status' => 'pending',
                'sent_by_agent_id' => $agent->id,
            ]);
            SendWhatsAppMessage::dispatch($message->id);
        }

        // Bot respondió: la pelota queda del lado del cliente (salvo que el
        // agente haya cerrado la conversación en este mismo turno)
        $conversation->update([
            'last_message_at' => now(),
            'last_message_preview' => Str::limit(end($chunks) ?: $text, 120),
            'status' => $conversation->status === 'cerrada' ? 'cerrada' : 'esperando_cliente',
        ]);
    }

    /**
     * Pasa la conversacion a modo humano y la deja como "nueva" para que
     * aparezca sin asignar en la bandeja.
     */
    protected function escalate(WaConversation $conversation, ?AiAgent $agent, string $motivo, bool $sendFallback = true): void
    {
        if ($sendFallback && $agent && $agent->mensaje_derivacion && $conversation->isSessionOpen()) {
            $this->reply($conversation, $agent, $agent->mensaje_derivacion);
        }

        $conversation->update(['mode' => 'humano', 'status' => 'nueva']);

        $conversation->messages()->create([
            'direction' => 'out',
            'type' => 'text',
            'body' => '🤖→👤 Derivado a humano: ' . $motivo,
            'is_internal_note' => true,
            'sent_by_agent_id' => $agent->id ?? null,
        ]);
    }

    protected function estimateCost(string $model, int $promptTokens, int $completionTokens): float
    {
        $pricing = [0.15, 0.60]; // default conservador
        foreach (self::PRICING as $prefix => $rates) {
            if (str_starts_with($model, $prefix)) {
                $pricing = $rates;
                break;
            }
        }

        return round(($promptTokens * $pricing[0] + $completionTokens * $pricing[1]) / 1_000_000, 4);
    }
}
