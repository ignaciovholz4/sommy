<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompraAdjunto extends Model
{
    protected $table = 'compra_adjuntos';

    protected $fillable = [
        'compra_id',
        'path',
        'original_name',
        'mime',
        'size',
        'user_id',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id', 'idcompra');
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
