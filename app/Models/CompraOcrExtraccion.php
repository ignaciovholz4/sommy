<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraOcrExtraccion extends Model
{
    protected $table = 'compra_ocr_extracciones';

    protected $fillable = [
        'user_id',
        'archivo_path',
        'mime',
        'proveedor_extraido',
        'proveedor_id_matched',
        'fecha_extraida',
        'num_folio_extraido',
        'tipo_comprobante_sugerido',
        'items_json',
        'confianza',
        'compra_id',
    ];

    protected $casts = [
        'items_json' => 'array',
        'fecha_extraida' => 'date',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id_matched', 'idproveedor');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id', 'idcompra');
    }
}
