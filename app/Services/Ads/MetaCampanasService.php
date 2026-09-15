<?php

namespace App\Services\Ads;

use App\Models\AdSpendDiario;
use App\Models\MetaAdsAuditoria;
use App\Models\MetaAdsConfig;
use App\Services\SolicitudAprobacionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Gestion de campanas de Meta Ads (crear/pausar/activar/presupuesto) via
 * Marketing API. Todas las acciones de escritura pasan por
 * SolicitudAprobacionService (doble aprobacion si quien la pide no es
 * superadmin) y quedan auditadas en meta_ads_auditoria, exitosas o no.
 * El tope de gasto (meta_ads_config) se valida ANTES de generar la solicitud:
 * si lo supera, se rechaza ahi mismo y ni se llega a crear una pendiente.
 */
class MetaCampanasService
{
    public function habilitado(): bool
    {
        return (bool) config('services.meta_ads.enabled')
            && !empty(config('services.meta_ads.access_token'))
            && !empty(config('services.meta_ads.ad_account_id'));
    }

    /** Listado de campanas (solo lectura, no pasa por aprobacion). */
    public function listar(): array
    {
        if (!$this->habilitado()) {
            return [];
        }

        $version = config('services.meta_ads.graph_version', 'v21.0');
        $adAccountId = config('services.meta_ads.ad_account_id');

        try {
            $response = Http::withToken(config('services.meta_ads.access_token'))
                ->acceptJson()
                ->get("https://graph.facebook.com/{$version}/act_{$adAccountId}/campaigns", [
                    'fields' => 'id,name,status,effective_status,objective,daily_budget,lifetime_budget',
                    'limit' => 100,
                ])->throw();
        } catch (\Throwable $th) {
            Log::error('MetaCampanasService::listar: ' . $th->getMessage());
            return [];
        }

        return $response->json('data') ?? [];
    }

    /**
     * Adsets (conjuntos de anuncios) de una campana, con sus anuncios
     * anidados — el presupuesto real casi siempre vive acá (a nivel conjunto)
     * y no en la campana, que es donde antes se mostraba "—" aunque hubiera
     * plata cargada.
     */
    public function listarAdsets(string $campaignId): array
    {
        if (!$this->habilitado()) {
            return [];
        }

        $version = config('services.meta_ads.graph_version', 'v21.0');

        try {
            $response = Http::withToken(config('services.meta_ads.access_token'))
                ->acceptJson()
                ->get("https://graph.facebook.com/{$version}/{$campaignId}/adsets", [
                    'fields' => 'id,name,status,effective_status,daily_budget,lifetime_budget',
                    'limit' => 100,
                ])->throw();
        } catch (\Throwable $th) {
            Log::error('MetaCampanasService::listarAdsets: ' . $th->getMessage());
            return [];
        }

        $adsets = $response->json('data') ?? [];

        foreach ($adsets as &$adset) {
            $adset['ads'] = $this->listarAds($adset['id']);
        }

        return $adsets;
    }

    /** Anuncios (creatividades) de un conjunto de anuncios, con su miniatura si Meta ya la generó. */
    public function listarAds(string $adsetId): array
    {
        if (!$this->habilitado()) {
            return [];
        }

        $version = config('services.meta_ads.graph_version', 'v21.0');

        try {
            $response = Http::withToken(config('services.meta_ads.access_token'))
                ->acceptJson()
                ->get("https://graph.facebook.com/{$version}/{$adsetId}/ads", [
                    'fields' => 'id,name,status,effective_status,creative{thumbnail_url,object_story_spec}',
                    'limit' => 100,
                ])->throw();
        } catch (\Throwable $th) {
            Log::error('MetaCampanasService::listarAds: ' . $th->getMessage());
            return [];
        }

        return $response->json('data') ?? [];
    }

    // ---------------------------------------------------------------
    // Crear campana
    // ---------------------------------------------------------------

    public function solicitarCrearCampana(array $datos): array
    {
        $presupuestoDiario = (float) ($datos['presupuesto_diario'] ?? 0);
        $tope = MetaAdsConfig::actual()->tope_presupuesto_diario;

        if ($tope && $presupuestoDiario > (float) $tope) {
            $this->auditar('crear_campana', null, null, $datos['nombre'] ?? null, null, null, false,
                "Presupuesto diario \${$presupuestoDiario} supera el tope configurado (\${$tope}).");

            return ['ok' => false, 'mensaje' => "El presupuesto diario supera el tope configurado (\${$tope})."];
        }

        $resultado = app(SolicitudAprobacionService::class)->ejecutarOSolicitar(
            'meta_ads.crear_campana',
            "Crear campaña de Meta Ads \"{$datos['nombre']}\" (presupuesto diario \${$presupuestoDiario})",
            $datos,
            null,
            fn () => $this->crearCampanaEjecutar($datos, Auth::id())
        );

        return $this->respuestaDesdeSolicitud($resultado, 'Campaña creada (en pausa) en Meta Ads.');
    }

    public function crearCampanaEjecutar(array $datos, ?int $userId): array
    {
        $version = config('services.meta_ads.graph_version', 'v21.0');
        $token = config('services.meta_ads.access_token');
        $adAccountId = config('services.meta_ads.ad_account_id');
        $base = "https://graph.facebook.com/{$version}/act_{$adAccountId}";

        try {
            // 1. Campana: SIEMPRE nace en pausa, la activacion es una accion aparte
            $campania = Http::withToken($token)->asForm()->post("{$base}/campaigns", [
                'name' => $datos['nombre'],
                'objective' => $datos['objetivo'] ?? 'OUTCOME_TRAFFIC',
                'status' => 'PAUSED',
                'special_ad_categories' => json_encode([]),
            ])->throw()->json();
            $campaignId = $campania['id'];

            // 2. Conjunto de anuncios (presupuesto + segmentacion)
            $adset = Http::withToken($token)->asForm()->post("{$base}/adsets", [
                'name' => $datos['nombre'] . ' - Conjunto',
                'campaign_id' => $campaignId,
                'daily_budget' => (int) round(((float) $datos['presupuesto_diario']) * 100), // Meta espera centavos
                'billing_event' => 'IMPRESSIONS',
                'optimization_goal' => $datos['optimization_goal'] ?? 'LINK_CLICKS',
                'targeting' => json_encode($datos['targeting'] ?? ['geo_locations' => ['countries' => ['AR']]]),
                'status' => 'PAUSED',
                'start_time' => $datos['start_time'] ?? now()->addMinutes(5)->toIso8601String(),
            ])->throw()->json();
            $adsetId = $adset['id'];

            // 3. Creativo subido por el usuario (imagen o video, manual, no generado con IA)
            $archivoPath = $datos['archivo_path'] ?? null;
            $objectStorySpec = [
                'page_id' => config('services.whatsapp.page_id'),
            ];

            if (($datos['es_video'] ?? false) && $archivoPath && Storage::exists($archivoPath)) {
                $videoId = Http::withToken($token)
                    ->attach('source', Storage::get($archivoPath), basename($archivoPath))
                    ->post("{$base}/advideos")->throw()->json('id');

                $thumbnailUrl = $this->esperarThumbnailVideo($token, $version, $videoId);

                $objectStorySpec['video_data'] = [
                    'video_id' => $videoId,
                    'image_url' => $thumbnailUrl,
                    'message' => $datos['texto'] ?? '',
                    'call_to_action' => [
                        'type' => 'LEARN_MORE',
                        'value' => ['link' => $datos['link_destino'] ?? url('/')],
                    ],
                ];
            } elseif ($archivoPath && Storage::exists($archivoPath)) {
                $subida = Http::withToken($token)
                    ->attach('source', Storage::get($archivoPath), basename($archivoPath))
                    ->post("{$base}/adimages")->throw()->json();
                $imageHash = collect($subida['images'] ?? [])->first()['hash'] ?? null;

                $objectStorySpec['link_data'] = [
                    'link' => $datos['link_destino'] ?? url('/'),
                    'message' => $datos['texto'] ?? '',
                    'image_hash' => $imageHash,
                ];
            }

            // 4. Creativo del anuncio
            $creative = Http::withToken($token)->asForm()->post("{$base}/adcreatives", [
                'name' => $datos['nombre'] . ' - Creativo',
                'object_story_spec' => json_encode($objectStorySpec),
            ])->throw()->json();
            $creativeId = $creative['id'];

            // 5. Anuncio (tambien en pausa)
            $ad = Http::withToken($token)->asForm()->post("{$base}/ads", [
                'name' => $datos['nombre'] . ' - Anuncio',
                'adset_id' => $adsetId,
                'creative' => json_encode(['creative_id' => $creativeId]),
                'status' => 'PAUSED',
            ])->throw()->json();

            $resultado = ['campaign_id' => $campaignId, 'adset_id' => $adsetId, 'ad_id' => $ad['id'] ?? null];

            $this->auditar('crear_campana', $campaignId, $adsetId, $datos['nombre'], null, $resultado, true, null, $userId);

            if ($archivoPath) {
                Storage::delete($archivoPath);
            }

            return $resultado;
        } catch (\Throwable $th) {
            Log::error('MetaCampanasService::crearCampanaEjecutar: ' . $th->getMessage());
            $this->auditar('crear_campana', null, null, $datos['nombre'] ?? null, null, null, false, $th->getMessage(), $userId);
            throw $th;
        }
    }

    /**
     * Meta procesa el video de forma asincronica: el thumbnail (obligatorio
     * para el creativo de video) tarda unos segundos en estar listo. Reintenta
     * unas pocas veces antes de rendirse.
     */
    private function esperarThumbnailVideo(string $token, string $version, string $videoId): ?string
    {
        for ($intento = 0; $intento < 8; $intento++) {
            $thumbnails = Http::withToken($token)
                ->get("https://graph.facebook.com/{$version}/{$videoId}/thumbnails")
                ->json('data') ?? [];

            if (!empty($thumbnails)) {
                $preferido = collect($thumbnails)->firstWhere('is_preferred', true) ?? $thumbnails[0];
                return $preferido['uri'] ?? null;
            }

            sleep(3);
        }

        throw new \RuntimeException('El video todavía se está procesando en Meta (sin thumbnail disponible). Probá crear la campaña de nuevo en un minuto.');
    }

    // ---------------------------------------------------------------
    // Pausar / activar
    // ---------------------------------------------------------------

    public function solicitarCambiarEstado(string $campaignId, string $nuevoEstado, ?string $nombreCampana = null): array
    {
        if ($nuevoEstado === 'ACTIVE') {
            $topeCuenta = MetaAdsConfig::actual()->tope_gasto_diario_cuenta;
            if ($topeCuenta) {
                $gastoHoy = (float) AdSpendDiario::where('plataforma', 'meta')->where('fecha', now()->toDateString())->sum('monto');
                if ($gastoHoy >= (float) $topeCuenta) {
                    $this->auditar('activar', $campaignId, null, $nombreCampana, null, null, false,
                        "Corte de emergencia: la cuenta ya gastó \${$gastoHoy} hoy (tope \${$topeCuenta}).");

                    return ['ok' => false, 'mensaje' => "No se puede activar: la cuenta ya gastó \${$gastoHoy} hoy, tope configurado \${$topeCuenta}."];
                }
            }
        }

        $datos = ['campaign_id' => $campaignId, 'nuevo_estado' => $nuevoEstado, 'nombre_campana' => $nombreCampana];
        $verbo = $nuevoEstado === 'ACTIVE' ? 'Activar' : 'Pausar';

        $resultado = app(SolicitudAprobacionService::class)->ejecutarOSolicitar(
            'meta_ads.cambiar_estado',
            "{$verbo} campaña de Meta Ads" . ($nombreCampana ? " \"{$nombreCampana}\"" : ''),
            $datos,
            null,
            fn () => $this->cambiarEstadoEjecutar($datos, Auth::id())
        );

        return $this->respuestaDesdeSolicitud($resultado, 'Estado actualizado en Meta Ads.');
    }

    public function cambiarEstadoEjecutar(array $datos, ?int $userId): array
    {
        $version = config('services.meta_ads.graph_version', 'v21.0');
        $accion = $datos['nuevo_estado'] === 'ACTIVE' ? 'activar' : 'pausar';

        try {
            Http::withToken(config('services.meta_ads.access_token'))->asForm()
                ->post("https://graph.facebook.com/{$version}/{$datos['campaign_id']}", [
                    'status' => $datos['nuevo_estado'],
                ])->throw();

            $this->auditar($accion, $datos['campaign_id'], null, $datos['nombre_campana'] ?? null,
                null, ['status' => $datos['nuevo_estado']], true, null, $userId);

            return ['campaign_id' => $datos['campaign_id'], 'status' => $datos['nuevo_estado']];
        } catch (\Throwable $th) {
            Log::error('MetaCampanasService::cambiarEstadoEjecutar: ' . $th->getMessage());
            $this->auditar($accion, $datos['campaign_id'], null, $datos['nombre_campana'] ?? null, null, null, false, $th->getMessage(), $userId);
            throw $th;
        }
    }

    // ---------------------------------------------------------------
    // Presupuesto
    // ---------------------------------------------------------------

    public function solicitarActualizarPresupuesto(string $adsetId, float $nuevoPresupuestoDiario, ?string $nombreCampana = null): array
    {
        $tope = MetaAdsConfig::actual()->tope_presupuesto_diario;
        if ($tope && $nuevoPresupuestoDiario > (float) $tope) {
            $this->auditar('subir_presupuesto', null, $adsetId, $nombreCampana, null, null, false,
                "Presupuesto \${$nuevoPresupuestoDiario} supera el tope configurado (\${$tope}).");

            return ['ok' => false, 'mensaje' => "El presupuesto supera el tope configurado (\${$tope})."];
        }

        $datos = ['adset_id' => $adsetId, 'nuevo_presupuesto_diario' => $nuevoPresupuestoDiario, 'nombre_campana' => $nombreCampana];

        $resultado = app(SolicitudAprobacionService::class)->ejecutarOSolicitar(
            'meta_ads.actualizar_presupuesto',
            "Actualizar presupuesto diario a \${$nuevoPresupuestoDiario}" . ($nombreCampana ? " en \"{$nombreCampana}\"" : ''),
            $datos,
            null,
            fn () => $this->actualizarPresupuestoEjecutar($datos, Auth::id())
        );

        return $this->respuestaDesdeSolicitud($resultado, 'Presupuesto actualizado en Meta Ads.');
    }

    public function actualizarPresupuestoEjecutar(array $datos, ?int $userId): array
    {
        $version = config('services.meta_ads.graph_version', 'v21.0');

        try {
            Http::withToken(config('services.meta_ads.access_token'))->asForm()
                ->post("https://graph.facebook.com/{$version}/{$datos['adset_id']}", [
                    'daily_budget' => (int) round(((float) $datos['nuevo_presupuesto_diario']) * 100),
                ])->throw();

            $this->auditar('subir_presupuesto', null, $datos['adset_id'], $datos['nombre_campana'] ?? null,
                null, ['daily_budget' => $datos['nuevo_presupuesto_diario']], true, null, $userId);

            return ['adset_id' => $datos['adset_id'], 'daily_budget' => $datos['nuevo_presupuesto_diario']];
        } catch (\Throwable $th) {
            Log::error('MetaCampanasService::actualizarPresupuestoEjecutar: ' . $th->getMessage());
            $this->auditar('subir_presupuesto', null, $datos['adset_id'], $datos['nombre_campana'] ?? null, null, null, false, $th->getMessage(), $userId);
            throw $th;
        }
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function respuestaDesdeSolicitud(array $resultado, string $mensajeOk): array
    {
        if ($resultado['ejecutado']) {
            return ['ok' => true, 'pendiente' => false, 'mensaje' => $mensajeOk, 'resultado' => $resultado['resultado'] ?? null];
        }

        return [
            'ok' => true,
            'pendiente' => true,
            'mensaje' => 'Quedó pendiente de aprobación de otro usuario (ver /admin/solicitudes).',
            'solicitud_id' => $resultado['solicitud']->id,
        ];
    }

    private function auditar(string $accion, ?string $campaignId, ?string $adsetId, ?string $nombreCampana, mixed $valorAnterior, mixed $valorNuevo, bool $exitoso, ?string $error, ?int $userId = null): void
    {
        MetaAdsAuditoria::create([
            'user_id' => $userId ?? Auth::id(),
            'accion' => $accion,
            'meta_campaign_id' => $campaignId,
            'meta_adset_id' => $adsetId,
            'nombre_campana' => $nombreCampana,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $valorNuevo,
            'exitoso' => $exitoso,
            'error' => $error,
        ]);
    }
}
