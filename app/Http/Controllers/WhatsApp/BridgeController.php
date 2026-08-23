<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

/**
 * Puente entre el backoffice y el bridge Node.js (Baileys): estado de la
 * sesion y QR de vinculacion, para conectar el numero escaneando desde el CRM
 * sin acceso a la consola del servidor.
 */
class BridgeController extends Controller
{
    protected function bridge()
    {
        return Http::withToken(config('services.whatsapp_baileys.bridge_token'))
            ->timeout(8)
            ->baseUrl(rtrim(config('services.whatsapp_baileys.bridge_url'), '/'));
    }

    /** Estado de la sesion: bridge caido / esperando QR / conectado. */
    public function estado()
    {
        try {
            $health = $this->bridge()->get('/health');
            if ($health->failed()) {
                return response()->json(['estado' => 'apagado']);
            }
            return response()->json([
                'estado' => $health->json('connected') ? 'conectado' : 'esperando_qr',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['estado' => 'apagado']);
        }
    }

    /** QR vigente como PNG (passthrough del bridge). */
    public function qr()
    {
        try {
            $resp = $this->bridge()->get('/qr');

            if ($resp->status() === 204) {
                return response()->json(['estado' => 'conectado'], 200);
            }
            if ($resp->status() === 202) {
                return response()->json(['estado' => 'esperando_qr'], 202);
            }
            if ($resp->successful()) {
                return response($resp->body(), 200)->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'no-store');
            }
            return response()->json(['estado' => 'error', 'detalle' => $resp->body()], 502);
        } catch (\Throwable $e) {
            return response()->json(['estado' => 'apagado'], 502);
        }
    }
}
