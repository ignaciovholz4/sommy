<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CobranzaRecordatorio extends Model
{
    protected $table = 'cobranza_recordatorios';

    protected $fillable = [
        'cliente_id', 'monto_vencido', 'dias_vencido', 'tier',
        'wa_template_id', 'template_params', 'nota_interna', 'estado',
        'wa_conversation_id', 'wa_message_id', 'revisado_por',
    ];

    protected $casts = [
        'template_params' => 'array',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'idcliente');
    }

    public function template()
    {
        return $this->belongsTo(WaTemplate::class, 'wa_template_id');
    }

    public function conversation()
    {
        return $this->belongsTo(WaConversation::class, 'wa_conversation_id');
    }

    public function revisadoPor()
    {
        return $this->belongsTo(\App\User::class, 'revisado_por');
    }
}
