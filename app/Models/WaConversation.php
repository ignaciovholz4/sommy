<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class WaConversation extends Model
{
    protected $table = 'wa_conversations';

    protected $fillable = [
        'wa_account_id', 'channel', 'external_id', 'cliente_id', 'phone_e164', 'profile_name',
        'status', 'assigned_user_id', 'ai_agent_id', 'mode',
        'last_inbound_at', 'last_message_at', 'last_message_preview', 'unread_count',
    ];

    public const CHANNELS = [
        'whatsapp'  => 'WhatsApp',
        'messenger' => 'Facebook',
        'instagram' => 'Instagram',
    ];

    protected $casts = [
        'last_inbound_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    public const STATUSES = [
        'nueva'             => 'Nueva',
        'en_atencion'       => 'En atención',
        'esperando_cliente' => 'Esperando cliente',
        'cerrada'           => 'Cerrada',
    ];

    public function account()
    {
        return $this->belongsTo(WaAccount::class, 'wa_account_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'idcliente');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function aiAgent()
    {
        return $this->belongsTo(AiAgent::class, 'ai_agent_id');
    }

    public function messages()
    {
        return $this->hasMany(WaMessage::class, 'conversation_id');
    }

    public function tags()
    {
        return $this->belongsToMany(WaTag::class, 'wa_conversation_tag', 'conversation_id', 'tag_id');
    }

    public function orderDrafts()
    {
        return $this->hasMany(WaOrderDraft::class, 'conversation_id');
    }

    /**
     * Ventana de 24 h de Meta: solo se puede mandar texto libre si el cliente
     * escribio hace menos de 24 h. Fuera de eso, solo plantillas aprobadas.
     */
    public function isSessionOpen(): bool
    {
        // La ventana de 24 h es una regla de la Cloud API de Meta. Por Baileys
        // (WhatsApp Web propio) no existe: siempre se puede escribir libre.
        if ($this->account && $this->account->provider === 'baileys') {
            return true;
        }

        return $this->last_inbound_at !== null
            && $this->last_inbound_at->gt(now()->subHours(24));
    }
}
