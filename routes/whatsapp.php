<?php

use App\Http\Controllers\WhatsApp\AiAgentController;
use App\Http\Controllers\WhatsApp\ConversationController;
use App\Http\Controllers\WhatsApp\OrderDraftController;
use App\Http\Controllers\WhatsApp\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WhatsApp / CRM / Agentes IA (backoffice)
|--------------------------------------------------------------------------
| Incluido desde tenant-routes.php dentro del grupo web.
*/

Route::middleware(['auth', 'verified'])->prefix('whatsapp')->name('whatsapp.')->group(function () {

    // Bandeja
    Route::get('/', [ConversationController::class, 'index'])->name('inbox');

    // Conexion del numero via bridge Baileys (QR desde el CRM)
    Route::get('/bridge/estado', [\App\Http\Controllers\WhatsApp\BridgeController::class, 'estado'])->name('bridge.estado');
    Route::get('/bridge/qr', [\App\Http\Controllers\WhatsApp\BridgeController::class, 'qr'])->name('bridge.qr');
    Route::get('/tablero', [ConversationController::class, 'board'])->name('board');
    Route::get('/conversations', [ConversationController::class, 'list'])->name('conversations.list');
    Route::get('/conversations/{id}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/{id}/send', [ConversationController::class, 'send'])->name('conversations.send');
    Route::post('/conversations/{id}/note', [ConversationController::class, 'note'])->name('conversations.note');
    Route::post('/conversations/{id}/assign', [ConversationController::class, 'assign'])->name('conversations.assign');
    Route::post('/conversations/{id}/status', [ConversationController::class, 'setStatus'])->name('conversations.status');
    Route::post('/conversations/{id}/mode', [ConversationController::class, 'toggleMode'])->name('conversations.mode');
    Route::post('/conversations/{id}/link-cliente', [ConversationController::class, 'linkCliente'])->name('conversations.linkCliente');
    Route::post('/conversations/{id}/tag', [ConversationController::class, 'toggleTag'])->name('conversations.tag');
    Route::get('/clientes/buscar', [ConversationController::class, 'searchClientes'])->name('clientes.buscar');
    Route::get('/media/{messageId}', [ConversationController::class, 'media'])->name('media');

    // Etiquetas
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::delete('/tags/{id}', [TagController::class, 'destroy'])->name('tags.destroy');


    // Borradores de pedido del bot
    Route::get('/drafts/pending', [OrderDraftController::class, 'pending'])->name('drafts.pending');
    Route::get('/drafts/{id}', [OrderDraftController::class, 'show'])->name('drafts.show');
    Route::post('/drafts/{id}/confirm', [OrderDraftController::class, 'confirm'])->name('drafts.confirm');
    Route::post('/drafts/{id}/reject', [OrderDraftController::class, 'reject'])->name('drafts.reject');

    // Agentes IA
    Route::get('/agents', [AiAgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/create', [AiAgentController::class, 'create'])->name('agents.create');
    Route::post('/agents', [AiAgentController::class, 'store'])->name('agents.store');
    Route::get('/agents/{id}/edit', [AiAgentController::class, 'edit'])->name('agents.edit');
    Route::put('/agents/{id}', [AiAgentController::class, 'update'])->name('agents.update');
    Route::post('/agents/{id}/toggle', [AiAgentController::class, 'toggle'])->name('agents.toggle');
    Route::delete('/agents/{id}', [AiAgentController::class, 'destroy'])->name('agents.destroy');
    Route::get('/agents/{id}/runs', [AiAgentController::class, 'runs'])->name('agents.runs');
});
