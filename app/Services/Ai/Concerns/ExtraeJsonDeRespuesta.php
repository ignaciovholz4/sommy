<?php

namespace App\Services\Ai\Concerns;

/**
 * Extrae un objeto JSON de una respuesta de texto libre de un LLM, tolerando
 * que venga envuelto en ```json ... ``` o con texto alrededor.
 */
trait ExtraeJsonDeRespuesta
{
    protected function extraerJson(string $texto): array
    {
        if (preg_match('/\{.*\}/s', $texto, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data)) {
                return $data;
            }
        }

        throw new \RuntimeException('La IA no devolvio un JSON valido: ' . mb_substr($texto, 0, 200));
    }
}
