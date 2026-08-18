<?php

namespace App\Support;

/**
 * Normalizacion de telefonos argentinos al formato que usa Meta (wa_id):
 * 549 + codigo de area + numero, sin "+", sin "15", sin "0" inicial.
 */
class PhoneAr
{
    /**
     * Convierte cualquier formato local a "549XXXXXXXXXX" (o null si no se puede).
     * Ejemplos: "011 15-3456-7890" => "5491134567890"; "+54 9 11 3456 7890" => "5491134567890"
     */
    public static function toE164(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') {
            return null;
        }

        // Ya viene con 54
        if (str_starts_with($digits, '54')) {
            $rest = substr($digits, 2);
            if (str_starts_with($rest, '9')) {
                $rest = substr($rest, 1);
            }
            $local = self::normalizeLocal($rest);
            return $local ? '549' . $local : null;
        }

        $local = self::normalizeLocal($digits);
        return $local ? '549' . $local : null;
    }

    /**
     * Deja el numero local en 10 digitos: area + abonado, sin 0 inicial ni 15.
     */
    protected static function normalizeLocal(string $digits): ?string
    {
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // Quitar el "15" que va despues del codigo de area en el formato viejo.
        // area(2-4 digitos) + 15 + abonado => 12 digitos; sin 15 => 10 digitos.
        if (strlen($digits) === 12) {
            foreach ([2, 3, 4] as $areaLen) {
                if (substr($digits, $areaLen, 2) === '15') {
                    $digits = substr($digits, 0, $areaLen) . substr($digits, $areaLen + 2);
                    break;
                }
            }
        }

        // Un numero local valido queda en 10 digitos (area + abonado)
        if (strlen($digits) === 10) {
            return $digits;
        }

        return null;
    }

    /**
     * Ultimos 10 digitos, para matching tolerante contra telefonos guardados
     * en cualquier formato en `clientes.telefono`.
     */
    public static function last10(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $raw);
        if (strlen($digits) < 8) {
            return null;
        }
        return substr($digits, -10);
    }

    /**
     * Formato legible: +54 9 11 3456-7890
     */
    public static function pretty(?string $e164): ?string
    {
        if (!$e164) {
            return null;
        }
        return '+' . $e164;
    }
}
