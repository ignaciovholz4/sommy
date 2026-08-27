<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Adjunto extends Model
{
    protected $table = 'adjuntos';

    protected $fillable = [
        'adjuntable_type',
        'adjuntable_id',
        'path',
        'original_name',
        'mime',
        'size',
        'user_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getEsImagenAttribute(): bool
    {
        return Str::startsWith((string) $this->mime, 'image/');
    }
}
