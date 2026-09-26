<?php

namespace App\Services\CreativeStudio\PromptEngine;

/**
 * Restricciones fijas que se agregan SIEMPRE a un prompt de generación con
 * producto real, para que el modelo nunca rediseñe el colchón. Automáticas:
 * el usuario no tiene que escribirlas ni acordarse de pedirlas.
 */
class NegativeRulesBuilder
{
    public static function paraProducto(): string
    {
        $reglas = [
            'no rediseñar el colchón',
            'no cambiar las costuras',
            'no cambiar el acolchado/quilting',
            'no modificar el piping',
            'no modificar el pillow top',
            'no agregar agarraderas ni manijas laterales (los colchones reales de Sommy no tienen)',
            'no aumentar ni reducir la altura del colchón',
            'no inventar capas que no existan',
            'no inventar logos ni textos sobre el colchón',
            'no modificar el patrón de la tela',
            'no distorsionar las proporciones',
            'no crear una perspectiva imposible',
            'no reemplazar el producto por otro colchón',
            'el colchón no puede quedar flotando',
            'nada de ambientes de fantasía',
            'nada de estética "hotel de lujo" salvo que se haya elegido esa escena explícitamente',
            'nada de elementos dorados excesivos',
            'nada de estética genérica de showroom de IA',
        ];

        return 'Restricciones obligatorias: ' . implode('; ', $reglas) . '.';
    }

    /** Reglas negativas extra que el usuario haya cargado en Mi marca (opcionales, se suman a las fijas). */
    public static function extra(): string
    {
        $extra = \Illuminate\Support\Facades\DB::table('publicaciones_ajustes')->value('reglas_negativas_extra');

        return trim((string) $extra) !== '' ? trim($extra) : '';
    }
}
