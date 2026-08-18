<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsAppWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook publico de la WhatsApp Cloud API (Meta).
 * GET  = verificacion inicial (hub.challenge)
 * POST = mensajes entrantes y estados de entrega
 */
class WebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        if (!$this->validSignature($request)) {
            Log::warning('Webhook WhatsApp con firma invalida', ['ip' => $request->ip()]);
            return response('Invalid signature', 401);
        }

        $payload = $request->json()->all();

        // whatsapp_business_account = WhatsApp; page = Messenger; instagram = IG Direct
        if (in_array($payload['object'] ?? '', ['whatsapp_business_account', 'page', 'instagram'], true)) {
            ProcessWhatsAppWebhook::dispatch($payload);
        }

        // Siempre 200 rapido: si Meta no recibe 200 reintenta y puede deshabilitar el webhook
        return response()->json(['status' => 'ok']);
    }

    protected function validSignature(Request $request): bool
    {
        // La app principal firma WhatsApp/Messenger; el producto "API de Instagram"
        // tiene app y clave propias y firma sus webhooks con esa otra clave.
        $secrets = array_filter([
            config('services.whatsapp.app_secret'),
            config('services.whatsapp.ig_app_secret'),
        ]);

        if (!$secrets) {
            // Sin app secret configurado (desarrollo temprano) no se valida firma
            return true;
        }

        $signature = $request->header('X-Hub-Signature-256', '');
        if (!str_starts_with($signature, 'sha256=')) {
            return false;
        }

        foreach ($secrets as $secret) {
            $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }
}
