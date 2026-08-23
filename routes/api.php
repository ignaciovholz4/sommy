<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Devolucion\DevolucionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Webhook de WhatsApp Cloud API (Meta) — publico, validado por verify_token/firma
Route::get('/whatsapp/webhook', [\App\Http\Controllers\WhatsApp\WebhookController::class, 'verify']);
Route::post('/whatsapp/webhook', [\App\Http\Controllers\WhatsApp\WebhookController::class, 'handle']);

// Webhook del bridge Baileys (WhatsApp no oficial) — shared secret propio, no HMAC de Meta
Route::post('/whatsapp/baileys/webhook', [\App\Http\Controllers\WhatsApp\BaileysWebhookController::class, 'handle']);

// Rutas de las devoluciones — require auth
Route::middleware('auth:api')->prefix('devoluciones')->group(function () {
    Route::post('/anular-compra/{idcompra}', [DevolucionController::class, 'anularCompra'])
        ->name('devoluciones.anularCompra');

    Route::post('/anular-venta/{idventa}', [DevolucionController::class, 'anularVenta'])
        ->name('devoluciones.anularVenta');
});