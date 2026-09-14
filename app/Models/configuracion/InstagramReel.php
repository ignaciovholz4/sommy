<?php

namespace App\Models\configuracion;

use Illuminate\Database\Eloquent\Model;

class InstagramReel extends Model
{
    protected $table = 'instagram_reels';

    protected $fillable = [
        'url',
        'titulo',
        'orden',
        'status',
    ];

    /** Código corto del reel (ej: DdRwzc3JJm3), extraído de la URL. */
    public function getShortcodeAttribute(): ?string
    {
        if (preg_match('#instagram\.com/(?:reel|reels|p|tv)/([A-Za-z0-9_-]+)#i', (string) $this->url, $m)) {
            return $m[1];
        }

        return null;
    }

    /** URL del embed oficial de Instagram para este reel, o null si la URL cargada no es válida. */
    public function getEmbedUrlAttribute(): ?string
    {
        return $this->shortcode ? "https://www.instagram.com/reel/{$this->shortcode}/embed" : null;
    }
}
