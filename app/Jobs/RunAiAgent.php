<?php

namespace App\Jobs;

use App\Models\WaMessage;
use App\Services\Ai\AiAgentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Corre el agente de venta IA sobre un mensaje entrante.
 */
class RunAiAgent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // el propio servicio maneja errores y derivacion
    public int $timeout = 120;

    public int $waMessageId;

    public function __construct(int $waMessageId)
    {
        $this->waMessageId = $waMessageId;
    }

    public function handle(AiAgentService $service): void
    {
        $message = WaMessage::with('conversation.aiAgent')->find($this->waMessageId);
        if (!$message) {
            return;
        }

        $service->handle($message);
    }
}
