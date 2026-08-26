<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $table = 'notas';

    protected $fillable = [
        'contenido',
        'fecha_recordatorio',
        'completada',
        'notable_type',
        'notable_id',
        'user_id',
    ];

    protected $casts = [
        'fecha_recordatorio' => 'date',
        'completada'         => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function scopeGenerales($query)
    {
        return $query->whereNull('notable_type');
    }

    public function scopeDe($query, string $tipo, $id)
    {
        return $query->where('notable_type', $tipo)->where('notable_id', $id);
    }

    public function getVencidaAttribute(): bool
    {
        return !$this->completada
            && $this->fecha_recordatorio
            && $this->fecha_recordatorio->isPast()
            && !$this->fecha_recordatorio->isToday();
    }
}
