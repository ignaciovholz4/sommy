<?php

namespace App\Services\Ai;

/**
 * Respuesta normalizada de un proveedor LLM.
 */
class LlmResponse
{
    /** @param array<int, array{id: string, name: string, arguments: array}> $toolCalls */
    public function __construct(
        public ?string $text,
        public array $toolCalls,
        public int $promptTokens,
        public int $completionTokens,
    ) {
    }

    public function hasToolCalls(): bool
    {
        return count($this->toolCalls) > 0;
    }
}
