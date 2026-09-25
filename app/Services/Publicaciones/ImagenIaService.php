<?php

namespace App\Services\Publicaciones;

use App\Services\CreativeStudio\AiProvider\GeminiImageProvider;
use App\Services\CreativeStudio\PromptEngine\PromptBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Fachada del chat sobre el motor compartido de Sommy Creative Studio
 * (PromptBuilder + GeminiImageProvider): arma el prompt con las reglas de
 * marca/fidelidad de producto y genera la(s) escena(s). El precio/logo NO
 * van en la imagen IA: los superpone el canvas del Estudio con datos
 * exactos del ERP.
 */
class ImagenIaService
{
    /** @deprecated usar PromptBuilder::ESCENAS */
    public const ESCENAS = PromptBuilder::ESCENAS;

    public function __construct(protected GeminiImageProvider $provider = new GeminiImageProvider())
    {
    }

    public function disponible(): bool
    {
        return $this->provider->disponible();
    }

    /** @deprecated usar PromptBuilder::paraProducto() directamente */
    public static function cuerpoPrompt(string $escena, ?string $estiloEntrenado): string
    {
        $cuerpo = (PromptBuilder::ESCENAS[$escena] ?? PromptBuilder::ESCENAS['dormitorio']) . '.';

        $cuerpo .= trim((string) $estiloEntrenado) !== ''
            ? ' Estilo de la marca: ' . trim($estiloEntrenado)
            : ' Estilo: fotografia comercial realista de alta calidad, colores serenos (azules, celestes, blancos), sin personas.';

        return $cuerpo;
    }

    public function generarEscena(string $rutaFotoProducto, string $escena, string $formato, string $instrucciones = '', ?string $promptLibre = null, ?string $extraEscena = null): array
    {
        $rutasReferencia = $this->rutasReferencia();
        $prompt = PromptBuilder::paraProducto($formato, $escena, $this->extraConInstrucciones($extraEscena, $instrucciones), (bool) $rutasReferencia, $promptLibre);

        $resultado = $this->provider->generateScene($prompt, $rutaFotoProducto, $rutasReferencia, $formato, 1);

        if (isset($resultado[0]['error'])) {
            throw new \RuntimeException($resultado[0]['error']);
        }

        return $resultado[0];
    }

    /**
     * Genera $cantidad variantes de la MISMA escena de marca (para elegir),
     * en paralelo. Cada llamada usa exactamente el mismo prompt fijo: la
     * variedad la da el propio muestreo de Gemini, no el texto.
     *
     * @return array<int, array{path:string,url:string,prompt:string}|array{error:string}>
     */
    public function generarVariantes(string $rutaFotoProducto, string $formato, int $cantidad = 5, string $instrucciones = '', ?string $extraEscena = null): array
    {
        $rutasReferencia = $this->rutasReferencia();
        $escena = 'dormitorio'; // único ambiente base: homogeneidad de feed
        $prompt = PromptBuilder::paraProducto($formato, $escena, $this->extraConInstrucciones($extraEscena, $instrucciones), (bool) $rutasReferencia);

        return $this->provider->generateScene($prompt, $rutaFotoProducto, $rutasReferencia, $formato, $cantidad);
    }

    /**
     * Modo Create (Sommy Creative Studio): genera $cantidad variantes combinando
     * objetivo/intensidad/escena/iluminación/cámara/composición/zona de texto,
     * con la misma fidelidad de producto y reglas negativas que el chat.
     *
     * @param array $opciones ver PromptBuilder::paraProductoStudio()
     * @return array<int, array{path:string,url:string,prompt:string}|array{error:string}>
     */
    public function generarVariantesStudio(string $rutaFotoProducto, string $formato, int $cantidad, array $opciones): array
    {
        $rutasReferencia = $this->rutasReferencia();
        $prompt = PromptBuilder::paraProductoStudio($formato, $opciones, (bool) $rutasReferencia);

        return $this->provider->generateScene($prompt, $rutaFotoProducto, $rutasReferencia, $formato, $cantidad);
    }

    /**
     * Contenido de marca SIN producto puntual (ej. estilo de vida, una persona
     * despertando descansada, un tip de descanso ilustrado). No hay foto real
     * que respetar, así que Gemini genera 100% desde texto — igual sigue el
     * estilo de marca fijo (Mi marca) para mantener el feed homogéneo.
     *
     * @return array<int, array{path:string,url:string,prompt:string}|array{error:string}>
     */
    public function generarVariantesMarca(string $formato, int $cantidad, string $instrucciones): array
    {
        $rutasReferencia = $this->rutasReferencia();
        $prompt = PromptBuilder::sinProducto($formato, $instrucciones, (bool) $rutasReferencia);

        return $this->provider->generateScene($prompt, null, $rutasReferencia, $formato, $cantidad);
    }

    protected function extraConInstrucciones(?string $extraEscena, string $instrucciones): ?string
    {
        $extra = trim((string) $extraEscena);
        if (trim($instrucciones) !== '') {
            $extra .= ($extra !== '' ? ' ' : '') . 'Indicacion extra: ' . trim($instrucciones);
        }

        return $extra !== '' ? $extra : null;
    }

    /** Rutas absolutas a imágenes de referencia de estilo activas (recurso tipo "referencia"), hasta 2. */
    protected function rutasReferencia(int $max = 2): array
    {
        $refs = DB::table('publicaciones_recursos')
            ->where('tipo', 'referencia')->where('activo', 1)
            ->whereNotNull('archivo')
            ->orderByDesc('id')->limit($max)->get(['archivo']);

        return $refs->map(fn ($r) => public_path($r->archivo))->filter('is_file')->values()->all();
    }
}
