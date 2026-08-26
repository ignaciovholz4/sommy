<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppMessage;
use App\Models\CobranzaRecordatorio;
use App\Services\WhatsApp\OutboundConversationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Cola de aprobacion de recordatorios de cobranza: el agente IA arma el
 * borrador, un humano lo revisa y decide aprobar (se envia por WhatsApp,
 * siempre via plantilla aprobada) o descartar. Nunca se envia solo.
 */
class CobranzaController extends Controller
{
    public function index()
    {
        Gate::authorize('haveaccess', 'finanzas.cobranzas.index');

        $pendientes = CobranzaRecordatorio::with(['cliente', 'template'])
            ->where('estado', 'pendiente_revision')
            ->orderByDesc('monto_vencido')
            ->get();

        $recientes = CobranzaRecordatorio::with(['cliente', 'template', 'revisadoPor'])
            ->whereIn('estado', ['enviado', 'descartado', 'fallido'])
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get();

        return view('finanzas.cobranzas.index', compact('pendientes', 'recientes'));
    }

    public function aprobarYEnviar($id, OutboundConversationService $outbound)
    {
        Gate::authorize('haveaccess', 'finanzas.cobranzas.aprobar');

        $recordatorio = CobranzaRecordatorio::with(['cliente', 'template'])->findOrFail($id);

        if ($recordatorio->estado !== 'pendiente_revision') {
            return back()->withErrors(['error' => 'Este recordatorio ya fue procesado.']);
        }

        $tpl = $recordatorio->template;
        if (!$tpl || $tpl->meta_status !== 'approved') {
            $recordatorio->update(['estado' => 'fallido']);
            return back()->withErrors(['error' => 'La plantilla de este recordatorio ya no está aprobada en Meta.']);
        }

        $conversation = $outbound->forCliente($recordatorio->cliente);
        if (!$conversation) {
            $recordatorio->update(['estado' => 'fallido']);
            return back()->withErrors(['error' => 'No se pudo determinar el WhatsApp del cliente (teléfono inválido o no hay cuenta de WhatsApp configurada).']);
        }

        $params = $recordatorio->template_params ?? [];
        $body = $tpl->body_text ?? "[plantilla {$tpl->name}]";
        foreach ($params as $i => $value) {
            $body = str_replace('{{' . ($i + 1) . '}}', $value, $body);
        }

        $message = $conversation->messages()->create([
            'direction' => 'out',
            'type' => 'template',
            'body' => $body,
            'status' => 'pending',
            'sent_by_user_id' => Auth::id(),
        ]);

        $conversation->fill([
            'mode' => 'humano',
            'status' => $conversation->status === 'nueva' ? 'en_atencion' : $conversation->status,
            'last_message_at' => now(),
            'last_message_preview' => Str::limit($body, 120),
        ]);
        if (!$conversation->assigned_user_id) {
            $conversation->assigned_user_id = Auth::id();
        }
        $conversation->save();

        SendWhatsAppMessage::dispatch($message->id, [
            'name' => $tpl->name,
            'language' => $tpl->language,
            'params' => $params,
        ]);

        $recordatorio->update([
            'estado' => 'enviado',
            'wa_conversation_id' => $conversation->id,
            'wa_message_id' => $message->id,
            'revisado_por' => Auth::id(),
        ]);

        return back()->with('success', 'Recordatorio enviado a ' . $recordatorio->cliente->nombre . '.');
    }

    public function descartar($id)
    {
        Gate::authorize('haveaccess', 'finanzas.cobranzas.descartar');

        $recordatorio = CobranzaRecordatorio::findOrFail($id);

        if ($recordatorio->estado !== 'pendiente_revision') {
            return back()->withErrors(['error' => 'Este recordatorio ya fue procesado.']);
        }

        $recordatorio->update(['estado' => 'descartado', 'revisado_por' => Auth::id()]);

        return back()->with('success', 'Recordatorio descartado.');
    }
}
