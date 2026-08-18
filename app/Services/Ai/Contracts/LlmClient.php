<?php

namespace App\Services\Ai\Contracts;

use App\Services\Ai\LlmResponse;

/**
 * Abstraccion de proveedor LLM con tool-calling.
 *
 * Formato neutral de mensajes:
 *  ['role' => 'user'|'assistant', 'content' => string]
 *  ['role' => 'assistant', 'content' => ?string, 'tool_calls' => [['id','name','arguments' => array]]]
 *  ['role' => 'tool', 'tool_call_id' => string, 'name' => string, 'content' => string]
 *
 * Formato neutral de tools:
 *  ['name' => string, 'description' => string, 'parameters' => JSON-schema array]
 */
interface LlmClient
{
    public function chat(
        string $system,
        array $messages,
        array $tools,
        string $model,
        float $temperature
    ): LlmResponse;
}
