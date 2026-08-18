<?php

namespace App\Services\Publicaciones;

use Illuminate\Support\Facades\Http;

/**
 * Publica imagenes en la pagina de Facebook y el feed de Instagram
 * via Graph API, reutilizando las credenciales de Meta ya configuradas
 * (services.whatsapp.page_id / page_token / ig_account_id).
 *
 * Facebook acepta subida directa del archivo (funciona desde localhost).
 * Instagram solo acepta una URL publica de imagen, por lo que requiere
 * que la app este accesible desde internet (produccion).
 */
class MetaPublisherService
{
    protected function base(): string
    {
        return 'https://graph.facebook.com/' . config('services.whatsapp.graph_version', 'v21.0');
    }

    protected function token(): ?string
    {
        return config('services.whatsapp.page_token');
    }

    public function facebookConfigurado(): bool
    {
        return $this->token() && config('services.whatsapp.page_id');
    }

    public function instagramConfigurado(): bool
    {
        return $this->token() && config('services.whatsapp.ig_account_id');
    }

    /** Sube una foto con texto a la pagina de Facebook. Devuelve el post_id. */
    public function publicarFacebook(string $rutaImagen, string $caption): string
    {
        if (!$this->facebookConfigurado()) {
            throw new \RuntimeException('Falta configurar FB_PAGE_ID / FB_PAGE_TOKEN en el .env');
        }

        $response = Http::withToken($this->token())
            ->attach('source', file_get_contents($rutaImagen), basename($rutaImagen))
            ->post($this->base() . '/' . config('services.whatsapp.page_id') . '/photos', [
                'caption' => $caption,
                'published' => 'true',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Facebook: ' . ($response->json('error.message') ?? $response->body()));
        }

        return (string) ($response->json('post_id') ?: $response->json('id'));
    }

    /** Sube un video con descripción a la página de Facebook. Devuelve el video_id. */
    public function publicarFacebookVideo(string $rutaVideo, string $descripcion): string
    {
        if (!$this->facebookConfigurado()) {
            throw new \RuntimeException('Falta configurar FB_PAGE_ID / FB_PAGE_TOKEN en el .env');
        }

        $response = Http::withToken($this->token())
            ->timeout(300)
            ->attach('source', file_get_contents($rutaVideo), basename($rutaVideo))
            ->post($this->base() . '/' . config('services.whatsapp.page_id') . '/videos', [
                'description' => $descripcion,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Facebook (video): ' . ($response->json('error.message') ?? $response->body()));
        }

        return (string) $response->json('id');
    }

    /** Publica en el feed de Instagram (container + publish). Devuelve el media_id. */
    public function publicarInstagram(string $urlPublicaImagen, string $caption): string
    {
        if (!$this->instagramConfigurado()) {
            throw new \RuntimeException('Falta configurar IG_ACCOUNT_ID / FB_PAGE_TOKEN en el .env');
        }

        $igId = config('services.whatsapp.ig_account_id');

        $container = Http::withToken($this->token())
            ->post($this->base() . "/{$igId}/media", [
                'image_url' => $urlPublicaImagen,
                'caption' => $caption,
            ]);

        if ($container->failed()) {
            throw new \RuntimeException('Instagram (container): ' . ($container->json('error.message') ?? $container->body())
                . ' — recorda que Instagram necesita que la imagen sea accesible publicamente (no funciona desde localhost).');
        }

        $publish = Http::withToken($this->token())
            ->post($this->base() . "/{$igId}/media_publish", [
                'creation_id' => $container->json('id'),
            ]);

        if ($publish->failed()) {
            throw new \RuntimeException('Instagram (publish): ' . ($publish->json('error.message') ?? $publish->body()));
        }

        return (string) $publish->json('id');
    }
}
