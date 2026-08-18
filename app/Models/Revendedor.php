<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revendedor extends Model
{
    protected $table = 'revendedores';

    protected $fillable = [
        'codigo', 'nombre', 'email', 'telefono', 'dni_cuit', 'localidad', 'provincia',
        'instagram', 'como_vende', 'comision_porcentaje', 'cbu', 'alias_cbu',
        'titular_cuenta', 'estado', 'notas', 'visitas', 'ultima_visita',
    ];

    protected $casts = [
        'comision_porcentaje' => 'float',
        'ultima_visita' => 'datetime',
    ];

    public function comisiones()
    {
        return $this->hasMany(RevendedorComision::class, 'revendedor_id');
    }

    public function pagos()
    {
        return $this->hasMany(RevendedorPago::class, 'revendedor_id');
    }

    /** Link público que el revendedor comparte */
    public function getLinkAttribute(): string
    {
        return rtrim(config('app.url'), '/') . '/r/' . $this->codigo;
    }

    /**
     * Genera un código corto, legible y único a partir del nombre.
     * Ej: "Juan Pérez" -> "JUAN4K2"
     */
    public static function generarCodigo(string $nombre): string
    {
        $base = strtoupper(preg_replace('/[^A-Za-z]/', '', \Illuminate\Support\Str::ascii($nombre)));
        $base = substr($base ?: 'SOMMY', 0, 6);

        do {
            $codigo = $base . strtoupper(\Illuminate\Support\Str::random(3));
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }
}
