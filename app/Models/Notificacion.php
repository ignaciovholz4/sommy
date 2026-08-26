<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Notificación del centro de novedades del negocio.
 * Se crean con Notificacion::avisar(...) desde los flujos del sistema.
 */
class Notificacion extends Model
{
    protected $table = 'notificaciones';

    public const UPDATED_AT = null;

    protected $fillable = ['tipo', 'titulo', 'mensaje', 'url', 'nivel', 'leida_at'];

    protected $casts = ['leida_at' => 'datetime', 'created_at' => 'datetime'];

    public const ICONOS = [
        'pedido'     => '🛒',
        'venta'      => '💵',
        'cobro'      => '✅',
        'entrega'    => '🚚',
        'devolucion' => '↩️',
        'stock'      => '⚠️',
        'reposicion' => '📦',
        'ceo'        => '🧭',
        'cheque'     => '📝',
        'solicitud'  => '🖐️',
    ];

    /**
     * Crea una notificación. Si ya existe una igual (mismo tipo + título)
     * en las últimas 12 horas, no duplica — evita spam de alertas repetidas.
     * Nunca rompe el flujo que la dispara.
     */
    public static function avisar(string $tipo, string $titulo, ?string $mensaje = null, ?string $url = null, string $nivel = 'info'): void
    {
        try {
            $repetida = static::where('tipo', $tipo)
                ->where('titulo', $titulo)
                ->where('created_at', '>=', now()->subHours(12))
                ->exists();

            if (!$repetida) {
                static::create(compact('tipo', 'titulo', 'mensaje', 'url', 'nivel'));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo crear la notificación: ' . $e->getMessage());
        }
    }
}
