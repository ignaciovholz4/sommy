<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    
    protected $table = 'clientes';
    protected $primaryKey = 'idcliente';
    public $timestamps = false;
    protected $fillable = [
        'nombre', 'direccion', 'localidad', 'provincia', 'codigo_postal',
        'telefono', 'email', 'dni_cuit', 'condicion_fiscal',
        'estatus', 'number_exterior', 'number_interior', 'materno', 'paterno',
    ];

    public const CONDICIONES_FISCALES = [
        'consumidor_final'      => 'Consumidor Final',
        'responsable_inscripto' => 'Responsable Inscripto',
        'monotributista'        => 'Monotributista',
        'exento'                => 'Exento',
    ];

    /**
     * Matching tolerante de telefono: compara los ultimos 10 digitos del numero
     * normalizado (formato Meta 549XXXXXXXXXX) contra `telefono` guardado en
     * cualquier formato local.
     */
    public function scopeWherePhoneMatches($query, ?string $e164)
    {
        $last10 = \App\Support\PhoneAr::last10($e164);
        if (!$last10) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereRaw(
            "RIGHT(REGEXP_REPLACE(COALESCE(telefono, ''), '[^0-9]', ''), 10) = ?",
            [$last10]
        );
    }
}
