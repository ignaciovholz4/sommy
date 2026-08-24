<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Cliente;
use App\Models\WaConversation;
use App\Models\WaMessage;
use App\Models\WaTag;
use App\Models\WaTemplate;
use App\Support\PhoneAr;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    /**
     * Vista principal de la bandeja.
     */
    public function index()
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $vendedores = User::where('estatus', 1)->orderBy('name')->get(['id', 'name']);
        $tags = WaTag::orderBy('nombre')->get();
        $templates = WaTemplate::approved()->orderBy('name')->get();

        return view('whatsapp.inbox', compact('vendedores', 'tags', 'templates'));
    }

    /**
     * Tablero de atención: conversaciones en columnas según qué tan
     * atendido está el cliente (mismo patrón que el board de envíos).
     */
    public function board()
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $base = WaConversation::with(['cliente', 'assignedUser', 'aiAgent'])
            ->orderByDesc('last_message_at');

        $sinAtender = (clone $base)->where('status', 'nueva')->get();
        $enAtencion = (clone $base)->where('status', 'en_atencion')->get();
        $esperando  = (clone $base)->where('status', 'esperando_cliente')->get();
        $cerradas   = (clone $base)->where('status', 'cerrada')
            ->where('last_message_at', '>=', now()->subDays(7))
            ->limit(30)->get();

        // Clientes con pedido en marcha (todavía no entregado ni cancelado):
        // sus tarjetas muestran el chip "Pedido en marcha"
        $clienteIds = collect([$sinAtender, $enAtencion, $esperando, $cerradas])
            ->flatten()->pluck('cliente_id')->filter()->unique();
        $conPedidoEnMarcha = \Illuminate\Support\Facades\DB::table('order_ecommerce')
            ->whereIn('cliente_id', $clienteIds)
            ->where('active', 1)
            ->whereNotIn('status_order_id', [5, 6]) // 5 Entregado, 6 Cancelado
            ->pluck('cliente_id')->unique()->flip();

        return view('whatsapp.board', compact('sinAtender', 'enAtencion', 'esperando', 'cerradas', 'conPedidoEnMarcha'));
    }

    /**
     * Listado de conversaciones (se refresca por polling).
     */
    public function list(Request $request)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $query = WaConversation::with(['cliente:idcliente,nombre,paterno', 'assignedUser:id,name', 'tags'])
            ->orderByDesc('last_message_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', '!=', 'cerrada');
        }
        if ($request->filled('assigned')) {
            if ($request->assigned === 'me') {
                $query->where('assigned_user_id', Auth::id());
            } elseif ($request->assigned === 'none') {
                $query->whereNull('assigned_user_id');
            } else {
                $query->where('assigned_user_id', (int) $request->assigned);
            }
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('wa_tags.id', (int) $request->tag));
        }
        if ($request->boolean('unread')) {
            $query->where('unread_count', '>', 0);
        }
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('phone_e164', 'like', "%{$q}%")
                    ->orWhere('external_id', 'like', "%{$q}%")
                    ->orWhere('profile_name', 'like', "%{$q}%")
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$q}%"));
            });
        }

        $conversations = $query->limit(80)->get()->map(fn ($c) => $this->conversationSummary($c));

        return response()->json(['conversations' => $conversations]);
    }

    /**
     * Detalle de una conversacion + mensajes nuevos (polling con after_id).
     */
    public function show(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $conversation = WaConversation::with(['cliente', 'assignedUser:id,name', 'aiAgent:id,nombre', 'tags'])
            ->findOrFail($id);

        $messagesQuery = $conversation->messages()->orderBy('id');
        if ($request->filled('after_id')) {
            $messagesQuery->where('id', '>', (int) $request->after_id);
        } else {
            // primeras cargas: ultimos 100
            $lastIds = $conversation->messages()->orderByDesc('id')->limit(100)->pluck('id');
            $messagesQuery->whereIn('id', $lastIds->all());
        }

        $messages = $messagesQuery->get()->map(fn ($m) => $this->messagePayload($m));

        // Al abrir la conversacion se marca como leida
        if (!$request->filled('after_id') && $conversation->unread_count > 0) {
            $conversation->update(['unread_count' => 0]);
        }

        $data = [
            'conversation' => $this->conversationSummary($conversation),
            'messages' => $messages,
            'session_open' => $conversation->isSessionOpen(),
        ];

        if (!$request->filled('after_id')) {
            $data['cliente'] = $this->clientePanel($conversation);
            $data['drafts'] = $conversation->orderDrafts()
                ->whereIn('status', ['borrador', 'pendiente_confirmacion'])
                ->orderByDesc('id')->get();
        }

        return response()->json($data);
    }

    /**
     * Enviar mensaje (texto libre dentro de la ventana de 24 h, o plantilla).
     */
    public function send(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'whatsapp.send');

        $conversation = WaConversation::findOrFail($id);

        $request->validate([
            'body' => 'required_without:template_id|nullable|string|max:4000',
            'template_id' => 'nullable|integer|exists:wa_templates,id',
            'template_params' => 'nullable|array',
        ]);

        $template = null;
        if ($request->filled('template_id') && $conversation->channel !== 'whatsapp') {
            return response()->json([
                'status' => 0,
                'mensaje' => 'Las plantillas solo existen en WhatsApp.',
            ], 422);
        }
        if ($request->filled('template_id')) {
            $tpl = WaTemplate::approved()->findOrFail($request->template_id);
            $params = array_values($request->template_params ?? []);
            $template = [
                'name' => $tpl->name,
                'language' => $tpl->language,
                'params' => $params,
            ];
            $body = $tpl->body_text ?? "[plantilla {$tpl->name}]";
            foreach ($params as $i => $value) {
                $body = str_replace('{{' . ($i + 1) . '}}', $value, $body);
            }
            $type = 'template';
        } else {
            // Regla de Meta: texto libre solo dentro de la ventana de 24 h
            if (!$conversation->isSessionOpen()) {
                $mensaje = $conversation->channel === 'whatsapp'
                    ? 'La ventana de 24 h está vencida: solo se pueden enviar plantillas aprobadas.'
                    : 'La ventana de 24 h está vencida: en ' . (WaConversation::CHANNELS[$conversation->channel] ?? $conversation->channel) . ' hay que esperar a que el cliente vuelva a escribir.';
                return response()->json(['status' => 0, 'mensaje' => $mensaje], 422);
            }
            $body = $request->body;
            $type = 'text';
        }

        $message = $conversation->messages()->create([
            'direction' => 'out',
            'type' => $type,
            'body' => $body,
            'status' => 'pending',
            'sent_by_user_id' => Auth::id(),
        ]);

        // Un humano respondio: el bot enmudece y la conversacion queda en atencion
        $conversation->fill([
            'mode' => 'humano',
            'status' => 'en_atencion',
            'last_message_at' => now(),
            'last_message_preview' => Str::limit($body, 120),
        ]);
        if (!$conversation->assigned_user_id) {
            $conversation->assigned_user_id = Auth::id();
        }
        $conversation->save();

        SendWhatsAppMessage::dispatch($message->id, $template);

        return response()->json(['status' => 1, 'message' => $this->messagePayload($message)]);
    }

    /**
     * Nota interna (no se envia al cliente).
     */
    public function note(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $request->validate(['body' => 'required|string|max:2000']);

        $conversation = WaConversation::findOrFail($id);
        $message = $conversation->messages()->create([
            'direction' => 'out',
            'type' => 'text',
            'body' => $request->body,
            'is_internal_note' => true,
            'sent_by_user_id' => Auth::id(),
        ]);

        return response()->json(['status' => 1, 'message' => $this->messagePayload($message)]);
    }

    public function assign(Request $request, $id)
    {
        $conversation = WaConversation::findOrFail($id);

        $userId = $request->input('user_id');
        if ((int) $userId !== Auth::id()) {
            Gate::authorize('haveaccess', 'whatsapp.assign');
        } else {
            Gate::authorize('haveaccess', 'whatsapp.index');
        }

        $conversation->update([
            'assigned_user_id' => $userId ?: null,
            'status' => $userId ? 'en_atencion' : $conversation->status,
            'mode' => $userId ? 'humano' : $conversation->mode,
        ]);

        return response()->json(['status' => 1, 'conversation' => $this->conversationSummary($conversation->fresh(['assignedUser', 'cliente', 'tags']))]);
    }

    public function setStatus(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $request->validate(['status' => 'required|in:nueva,en_atencion,esperando_cliente,cerrada']);

        $conversation = WaConversation::findOrFail($id);
        $conversation->update(['status' => $request->status]);

        return response()->json(['status' => 1]);
    }

    /**
     * Alternar bot/humano (handoff manual).
     */
    public function toggleMode(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $request->validate(['mode' => 'required|in:bot,humano']);

        $conversation = WaConversation::findOrFail($id);
        $conversation->update(['mode' => $request->mode]);

        return response()->json(['status' => 1, 'mode' => $conversation->mode]);
    }

    /**
     * Vincular la conversacion a un cliente existente o crear uno nuevo.
     */
    public function linkCliente(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $conversation = WaConversation::findOrFail($id);

        if ($request->filled('cliente_id')) {
            $cliente = Cliente::findOrFail($request->cliente_id);
        } else {
            $request->validate(['nombre' => 'required|string|max:100']);
            $cliente = Cliente::create([
                'nombre' => $request->nombre,
                'telefono' => PhoneAr::pretty($conversation->phone_e164) ?? '',
                'email' => $request->email ?? '',
                'direccion' => $request->direccion ?? '',
                'estatus' => 1,
            ]);
        }

        $conversation->update(['cliente_id' => $cliente->idcliente]);

        return response()->json(['status' => 1, 'cliente' => $this->clientePanel($conversation->fresh('cliente'))]);
    }

    /**
     * Buscador de clientes para el modal de vinculacion.
     */
    public function searchClientes(Request $request)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        return response()->json(
            Cliente::where('estatus', 1)
                ->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('telefono', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('dni_cuit', 'like', "%{$q}%");
                })
                ->limit(10)
                ->get(['idcliente', 'nombre', 'paterno', 'telefono', 'email'])
        );
    }

    public function toggleTag(Request $request, $id)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $request->validate(['tag_id' => 'required|integer|exists:wa_tags,id']);

        $conversation = WaConversation::findOrFail($id);
        $conversation->tags()->toggle([(int) $request->tag_id]);

        return response()->json(['status' => 1, 'tags' => $conversation->tags()->get()]);
    }

    /**
     * Sirve un adjunto descargado (imagen/audio/documento) con auth.
     */
    public function media($messageId)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $message = WaMessage::findOrFail($messageId);
        if (!$message->media_path || !Storage::disk('local')->exists($message->media_path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('local')->path($message->media_path),
            ['Content-Type' => $message->media_mime ?? 'application/octet-stream']
        );
    }

    // ------------------------------------------------------------------

    protected function conversationSummary(WaConversation $c): array
    {
        return [
            'id' => $c->id,
            'channel' => $c->channel,
            'phone' => $c->channel === 'whatsapp'
                ? PhoneAr::pretty($c->phone_e164)
                : (WaConversation::CHANNELS[$c->channel] ?? $c->channel),
            'profile_name' => $c->profile_name,
            'cliente_id' => $c->cliente_id,
            'cliente_nombre' => $c->cliente ? trim($c->cliente->nombre . ' ' . ($c->cliente->paterno ?? '')) : null,
            'status' => $c->status,
            'mode' => $c->mode,
            'assigned_user_id' => $c->assigned_user_id,
            'assigned_user' => $c->assignedUser->name ?? null,
            'unread_count' => $c->unread_count,
            'last_message_at' => optional($c->last_message_at)->format('Y-m-d H:i'),
            'last_message_human' => optional($c->last_message_at)->diffForHumans(),
            'last_message_preview' => $c->last_message_preview,
            'session_open' => $c->isSessionOpen(),
            'tags' => $c->relationLoaded('tags') ? $c->tags->map(fn ($t) => ['id' => $t->id, 'nombre' => $t->nombre, 'color' => $t->color]) : [],
        ];
    }

    protected function messagePayload(WaMessage $m): array
    {
        return [
            'id' => $m->id,
            'direction' => $m->direction,
            'type' => $m->type,
            'body' => $m->body,
            'is_internal_note' => $m->is_internal_note,
            'status' => $m->status,
            'error_detail' => $m->error_detail,
            'has_media' => (bool) $m->media_path,
            'media_url' => $m->media_path ? url("whatsapp/media/{$m->id}") : null,
            'media_mime' => $m->media_mime,
            'sent_by' => $m->sent_by_user_id ? optional($m->sentByUser)->name : ($m->sent_by_agent_id ? '🤖 ' . (optional($m->sentByAgent)->nombre ?? 'Bot') : null),
            'time' => optional($m->wa_timestamp ?? $m->created_at)->format('d/m H:i'),
        ];
    }

    protected function clientePanel(WaConversation $conversation): ?array
    {
        $cliente = $conversation->cliente;
        if (!$cliente) {
            return null;
        }

        $ultimosPedidos = \App\Models\ecommerce\order_ecommerce::where('cliente_id', $cliente->idcliente)
            ->orderByDesc('order_id')->limit(5)
            ->get(['order_id', 'order_date', 'total_amount', 'status_order_id', 'origen']);

        $saldoCc = (float) \Illuminate\Support\Facades\DB::table('cliente_cc_movimientos')
            ->where('cliente_id', $cliente->idcliente)
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo='cargo' THEN monto ELSE -monto END),0) as saldo")
            ->value('saldo');

        return [
            'idcliente' => $cliente->idcliente,
            'nombre' => trim($cliente->nombre . ' ' . ($cliente->paterno ?? '')),
            'telefono' => $cliente->telefono,
            'email' => $cliente->email,
            'direccion' => $cliente->direccion,
            'localidad' => $cliente->localidad,
            'condicion_fiscal' => $cliente->condicion_fiscal,
            'saldo_cc' => $saldoCc,
            'pedidos' => $ultimosPedidos,
        ];
    }
}
