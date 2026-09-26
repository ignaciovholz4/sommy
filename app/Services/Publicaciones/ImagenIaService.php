<?php

namespace App\Services\Publicaciones;

use App\Services\CreativeStudio\AiProvider\GeminiImageProvider;
use App\Services\CreativeStudio\PromptEngine\PromptBuilder;
use App\Services\CreativeStudio\ProductLibraryService;
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

    public function __construct(
        protected GeminiImageProvider $provider = new GeminiImageProvider(),
        protected ProductLibraryService $productos = new ProductLibraryService(),
    ) {
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

    public function generarEscena(string $rutaFotoProducto, string $escena, string $formato, string $instrucciones = '', ?string $promptLibre = null, ?string $extraEscena = null, ?array $producto = null, bool $conPrecio = false, ?string $headline = null): array
    {
        $rutasProducto = $this->rutasFidelidadProducto($rutaFotoProducto, $producto['id'] ?? null);
        $rutasReferencia = $this->rutasReferencia();
        $prompt = PromptBuilder::paraProducto($formato, $escena, $this->extraConInstrucciones($extraEscena, $instrucciones), (bool) $rutasReferencia, $promptLibre, $producto, $conPrecio, $headline, count($rutasProducto));

        $resultado = $this->generarYRegistrar($prompt, $rutasProducto, $rutasReferencia, $formato, 1);

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
    public function generarVariantes(string $rutaFotoProducto, string $formato, int $cantidad = 5, string $instrucciones = '', ?string $extraEscena = null, ?array $producto = null, bool $conPrecio = false, ?string $headline = null, bool $incluirFlete = false, bool $sinBanner = false): array
    {
        $rutasProducto = $this->rutasFidelidadProducto($rutaFotoProducto, $producto['id'] ?? null);
        if ($incluirFlete) {
            foreach ($this->productos->rutasFleteReal() as $ruta) {
                if (!in_array($ruta, $rutasProducto, true)) {
                    $rutasProducto[] = $ruta;
                }
            }
        }
        $rutasReferencia = $this->rutasReferencia();
        $escena = 'dormitorio'; // único ambiente base: homogeneidad de feed
        $prompt = PromptBuilder::paraProducto($formato, $escena, $this->extraConInstrucciones($extraEscena, $instrucciones), (bool) $rutasReferencia, null, $producto, $conPrecio, $headline, count($rutasProducto), $sinBanner);

        return $this->generarYRegistrar($prompt, $rutasProducto, $rutasReferencia, $formato, $cantidad);
    }

    /**
     * Modo Create (Sommy Creative Studio): genera $cantidad variantes combinando
     * objetivo/intensidad/escena/iluminación/cámara/composición/zona de texto,
     * con la misma fidelidad de producto y reglas negativas que el chat.
     *
     * @param array $opciones ver PromptBuilder::paraProductoStudio()
     * @return array<int, array{path:string,url:string,prompt:string}|array{error:string}>
     */
    public function generarVariantesStudio(string $rutaFotoProducto, string $formato, int $cantidad, array $opciones, ?array $producto = null, bool $conPrecio = false, ?string $headline = null): array
    {
        $rutasProducto = $this->rutasFidelidadProducto($rutaFotoProducto, $producto['id'] ?? null);
        $rutasReferencia = $this->rutasReferencia();
        $prompt = PromptBuilder::paraProductoStudio($formato, $opciones, (bool) $rutasReferencia, $producto, $conPrecio, $headline, count($rutasProducto));

        return $this->generarYRegistrar($prompt, $rutasProducto, $rutasReferencia, $formato, $cantidad);
    }

    /**
     * Contenido de marca SIN producto puntual (ej. estilo de vida, una persona
     * despertando descansada, un tip de descanso ilustrado). No hay foto real
     * que respetar, así que Gemini genera 100% desde texto — igual sigue el
     * estilo de marca fijo (Mi marca) para mantener el feed homogéneo.
     *
     * @return array<int, array{path:string,url:string,prompt:string}|array{error:string}>
     */
    public function generarVariantesMarca(string $formato, int $cantidad, string $instrucciones, bool $incluirFlete = false): array
    {
        $rutasReferencia = $this->rutasReferencia();
        $rutasFlete = $incluirFlete ? $this->productos->rutasFleteReal() : [];
        $prompt = PromptBuilder::sinProducto($formato, $instrucciones, (bool) $rutasReferencia, (bool) $rutasFlete);

        return $this->generarYRegistrar($prompt, $rutasFlete, $rutasReferencia, $formato, $cantidad);
    }

    /**
     * Todas las fotos reales a respetar para esta generación: la foto principal del
     * colchón, hasta 3 ángulos extra reales (galería producto_imagenes + fotos
     * cargadas en "Conocimiento del producto") y —si existe cargada en el
     * catálogo— la foto real de la base/sommier de Sommy. Nunca se inventa
     * ninguna: si no hay archivo real, no se manda nada de más.
     *
     * @return array<int, string>
     */
    protected function rutasFidelidadProducto(string $rutaFotoProducto, ?int $productoId): array
    {
        $rutas = [$rutaFotoProducto];

        $angulosExtra = array_merge(
            $this->productos->rutasAngulosExtra($productoId, $rutaFotoProducto),
            $this->productos->rutasConocimientoImagenes($productoId)
        );
        foreach (array_slice(array_unique($angulosExtra), 0, 3) as $ruta) {
            if (!in_array($ruta, $rutas, true)) {
                $rutas[] = $ruta;
            }
        }

        $sommier = $this->productos->rutaSommierReal();
        if ($sommier !== null && !in_array($sommier, $rutas, true)) {
            $rutas[] = $sommier;
        }

        return array_values(array_unique($rutas));
    }

    /** Genera y deja registro en publicaciones_generaciones (historial/reproducibilidad) de cada resultado, ok o error. */
    protected function generarYRegistrar(string $prompt, array $rutasProducto, array $rutasReferencia, string $formato, int $cantidad): array
    {
        $modelo = config('services.gemini.image_model', 'gemini-3.1-flash-lite-image');

        try {
            $resultados = $this->provider->generateScene($prompt, $rutasProducto, $rutasReferencia, $formato, $cantidad);
        } catch (\Throwable $e) {
            $this->registrarGeneracion($modelo, $formato, $prompt, 'error', $e->getMessage(), null);
            throw $e;
        }

        foreach ($resultados as $r) {
            $this->registrarGeneracion($modelo, $formato, $prompt, isset($r['error']) ? 'error' : 'ok', $r['error'] ?? null, $r['path'] ?? null);
        }

        return $resultados;
    }

    protected function registrarGeneracion(string $modelo, string $formato, string $prompt, string $estado, ?string $error, ?string $imagenPath): void
    {
        try {
            DB::table('publicaciones_generaciones')->insert([
                'modelo'       => $modelo,
                'formato'      => $formato,
                'prompt_final' => $prompt,
                'estado'       => $estado,
                'error'        => $error,
                'imagen_path'  => $imagenPath,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // El historial nunca debe romper una generación real.
        }
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

        return $refs->map(fn ($r) => public_path($r->archivo))->filter(fn ($ruta) => is_file($ruta))->values()->all();
    }
}
