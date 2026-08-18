<?php

namespace App\Jobs;

use App\Models\WaMessage;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Descarga el archivo adjunto de un mensaje entrante (las URLs de Meta
 * expiran en minutos, por eso se persiste localmente).
 */
class DownloadWaMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 20;

    public int $waMessageId;

    public function __construct(int $waMessageId)
    {
        $this->waMessageId = $waMessageId;
    }

    public function handle(): void
    {
        $message = WaMessage::with('conversation.account')->find($this->waMessageId);
        if (!$message || !$message->media_id || $message->media_path) {
            return;
        }

        // Messenger/Instagram entregan una URL directa del CDN (sin token);
        // WhatsApp entrega un media_id que hay que resolver contra la Graph API.
        if (str_starts_with($message->media_id, 'http')) {
            $response = \Illuminate\Support\Facades\Http::get($message->media_id);
            if (!$response->successful()) {
                return;
            }
            $binary = $response->body();
            $mime = $response->header('Content-Type') ?: ($message->media_mime ?? 'application/octet-stream');
            $mime = explode(';', $mime)[0];
        } else {
            $service = WhatsAppService::forAccount($message->conversation->account);

            $meta = $service->getMediaUrl($message->media_id);
            if (!$meta || empty($meta['url'])) {
                return;
            }

            $binary = $service->downloadMedia($meta['url']);
            if ($binary === null) {
                return;
            }

            $mime = $meta['mime_type'] ?? $message->media_mime ?? 'application/octet-stream';
        }
        $extension = self::extensionFor($mime);
        $filename = str_starts_with($message->media_id, 'http') ? md5($message->media_id) : $message->media_id;
        $path = 'wa-media/' . $message->conversation_id . '/' . $filename . $extension;

        Storage::disk('local')->put($path, $binary);

        $message->update([
            'media_path' => $path,
            'media_mime' => $mime,
        ]);
    }

    protected static function extensionFor(string $mime): string
    {
        return match (true) {
            str_contains($mime, 'jpeg') => '.jpg',
            str_contains($mime, 'png') => '.png',
            str_contains($mime, 'webp') => '.webp',
            str_contains($mime, 'ogg') => '.ogg',
            str_contains($mime, 'mpeg') => '.mp3',
            str_contains($mime, 'mp4') => '.mp4',
            str_contains($mime, 'pdf') => '.pdf',
            default => '',
        };
    }
}
