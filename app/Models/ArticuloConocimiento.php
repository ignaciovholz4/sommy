<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Conocimiento interno de un producto (no visible en el ecommerce):
 * contexto para el bot del CRM y el Estudio de Publicaciones.
 */
class ArticuloConocimiento extends Model
{
    protected $table = 'articulo_conocimiento';

    public const TIPOS = [
        'instrucciones'   => 'Instrucciones de uso',
        'caracteristicas' => 'Características técnicas',
        'faq'             => 'Pregunta frecuente',
        'nota'            => 'Nota interna',
        'imagen'          => 'Imagen',
        'video'           => 'Video',
        'audio'           => 'Audio',
        'documento'       => 'Documento',
    ];

    public const TIPOS_TEXTO = ['instrucciones', 'caracteristicas', 'faq', 'nota'];

    protected $fillable = ['articulo_id', 'tipo', 'titulo', 'contenido', 'archivo', 'mime', 'activo', 'prioridad'];

    protected $casts = ['activo' => 'boolean', 'prioridad' => 'integer'];

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id', 'idarticulo');
    }

    public function esTexto(): bool
    {
        return in_array($this->tipo, self::TIPOS_TEXTO);
    }

    public function getArchivoUrlAttribute(): ?string
    {
        return $this->archivo
            ? Storage::disk(config('services.conocimiento.disk', 'public'))->url($this->archivo)
            : null;
    }
}
