<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\LlmClient;
use Illuminate\Support\Facades\Http;

class AnthropicClient implements LlmClient
{
    public function chat(string $system, array $messages, array $tools, string $model, float $temperature): LlmResponse
    {
        $payload = [
            'model' => $model,
            'max_tokens' => 1024,
            'temperature' => min($temperature, 1.0),
            'system' => $system,
            'messages' => $this->convertMessages($messages),
        ];

        if ($tools) {
            $payload['tools'] = array_map(fn ($t) => [
                'name' => $t['name'],
                'description' => $t['description'],
                'input_schema' => $t['parameters'],
            ], $tools);
        }

        $response = Http::withHeaders([
                'x-api-key' => config('services.anthropic.api_key'),
                'anthropic-version' => '2023-06-01',
            ])
            ->timeout(90)
            ->post('https://api.anthropic.com/v1/messages', $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Anthropic error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $text = null;
        $toolCalls = [];
        foreach ($response->json('content') ?? [] as $block) {
            if ($block['type'] === 'text') {
                $text = trim(($text ?? '') . $block['text']);
            } elseif ($block['type'] === 'tool_use') {
                $toolCalls[] = [
                    'id' => $block['id'],
                    'name' => $block['name'],
                    'arguments' => $block['input'] ?? [],
                ];
            }
        }

        return new LlmResponse(
            $text,
            $toolCalls,
            (int) $response->json('usage.input_tokens', 0),
            (int) $response->json('usage.output_tokens', 0),
        );
    }

    /**
     * Convierte el formato neutral al de la Messages API (bloques de contenido).
     */
    protected function convertMessages(array $messages): array
    {
        $out = [];
        foreach ($messages as $message) {
            if ($message['role'] === 'assistant' && !empty($message['tool_calls'])) {
                $blocks = [];
                if (!empty($message['content'])) {
                    $blocks[] = ['type' => 'text', 'text' => $message['content']];
                }
                foreach ($message['tool_calls'] as $tc) {
                    $blocks[] = [
                        'type' => 'tool_use',
                        'id' => $tc['id'],
                        'name' => $tc['name'],
                        'input' => $tc['arguments'] ?: new \stdClass(),
                    ];
                }
                $out[] = ['role' => 'assistant', 'content' => $blocks];
                continue;
            }

            if ($message['role'] === 'tool') {
                // Anthropic espera los tool_result como mensaje de usuario
                $result = [
                    'type' => 'tool_result',
                    'tool_use_id' => $message['tool_call_id'],
                    'content' => $message['content'],
                ];
                // Agrupar tool_results consecutivos en un solo mensaje user
                $lastIndex = count($out) - 1;
                if ($lastIndex >= 0 && $out[$lastIndex]['role'] === 'user' && is_array($out[$lastIndex]['content'])
                    && ($out[$lastIndex]['content'][0]['type'] ?? '') === 'tool_result') {
                    $out[$lastIndex]['content'][] = $result;
                } else {
                    $out[] = ['role' => 'user', 'content' => [$result]];
                }
                continue;
            }

            $out[] = ['role' => $message['role'], 'content' => $message['content'] ?? ''];
        }

        return $out;
    }
}
