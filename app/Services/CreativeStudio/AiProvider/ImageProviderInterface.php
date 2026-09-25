<?php

namespace App\Services\CreativeStudio\AiProvider;

/**
 * Contrato para generar escenas con IA, independiente del proveedor.
 * Permite en el futuro sumar otro proveedor sin reescribir el resto de la
 * app (Prompt Engine, Product Library, controllers) — solo se implementa
 * esta interfaz.
 */
interface ImageProviderInterface
{
    /**
     * Genera $cantidad variantes de una escena a partir de un prompt ya armado
     * (por el Prompt Engine), opcionalmente con foto(s) de producto real y/o
     * imágenes de referencia de estilo, que van como partes adicionales.
     *
     * @param array<int, string> $rutasProducto rutas absolutas a fotos reales a respetar (colchón, distintos ángulos, base/sommier real), vacío si no hay producto
     * @param array<int, string> $rutasReferencia rutas absolutas a imágenes de referencia de estilo (opcional) — nunca fidelidad de producto
     * @return array<int, array{path:string,url:string,prompt:string}|array{error:string}>
     */
    public function generateScene(string $prompt, array $rutasProducto, array $rutasReferencia, string $aspectRatio, int $cantidad): array;
}
