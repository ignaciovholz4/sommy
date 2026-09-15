<?php

namespace App\Support;

/**
 * El dueño no quiere precios con centavos ni "sueltos" (ej. $141.847,33):
 * todo precio de venta se redondea siempre PARA ARRIBA al millar más
 * cercano (ej. $141.847,33 -> $142.000). Se usa tanto al guardar un precio
 * desde el panel como al calcular precios derivados (promos, descuentos de
 * combo), para que el número que ve el cliente sea siempre redondo.
 */
class Precio
{
    public static function redondear(float|int|string|null $monto): float
    {
        $monto = (float) $monto;
        if ($monto <= 0) {
            return $monto;
        }

        return ceil($monto / 1000) * 1000;
    }
}
