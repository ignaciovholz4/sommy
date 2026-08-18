<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\LlmClient;
use Illuminate\Support\Facades\Http;

class OpenAiClient implements LlmClient
{
    public function chat(string $system, array $messages, array $tools, string $model, float $temperature): LlmResponse
    {
        $payload = [
            'model' => $model,
            'temperature' => $temperature,
            'messages' => array_merge(
                [['role' => 'system', 'content' => $system]],
                array_map([$this, 'convertMessage'], $messages)
            ),
        ];

        if ($tools) {
            $payload['tools'] = array_map(fn ($t) => [
                'type' => 'function',
                'function' => [
                    'name' => $t['name'],
                    'description' => $t['description'],
                    'parameters' => $t['parameters'],
                ],
            ], $tools);
        }

        $response = Http::withToken(config('services.openai.api_key'))
            ->timeout(90)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $choice = $response->json('choices.0.message') ?? [];

        $toolCalls = array_map(fn ($tc) => [
            'id' => $tc['id'],
            'name' => $tc['function']['name'],
            'arguments' => json_decode($tc['function']['arguments'] ?? '{}', true) ?: [],
        ], $choice['tool_calls'] ?? []);

        return new LlmResponse(
            $choice['content'] ?? null,
            $toolCalls,
            (int) $response->json('usage.prompt_tokens', 0),
            (int) $response->json('usage.completion_tokens', 0),
        );
    }

    protected function convertMessage(array $message): array
    {
        if ($message['role'] === 'assistant' && !empty($message['tool_calls'])) {
            return [
                'role' => 'assistant',
                'content' => $message['content'] ?? null,
                'tool_calls' => array_map(fn ($tc) => [
                    'id' => $tc['id'],
                    'type' => 'function',
                    'function' => [
                        'name' => $tc['name'],
                        'arguments' => json_encode($tc['arguments'], JSON_UNESCAPED_UNICODE),
                    ],
                ], $message['tool_calls']),
            ];
        }

        if ($message['role'] === 'tool') {
            return [
                'role' => 'tool',
                'tool_call_id' => $message['tool_call_id'],
                'content' => $message['content'],
            ];
        }

        return ['role' => $message['role'], 'content' => $message['content'] ?? ''];
    }
}
