<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class WaMessage extends Model
{
    protected $table = 'wa_messages';

    protected $fillable = [
        'conversation_id', 'wa_message_id', 'direction', 'type', 'body',
        'media_id', 'media_path', 'media_mime',
        'status', 'error_code', 'error_detail',
        'sent_by_user_id', 'sent_by_agent_id', 'is_internal_note',
        'payload', 'wa_timestamp',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_internal_note' => 'boolean',
        'wa_timestamp' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(WaConversation::class, 'conversation_id');
    }

    public function sentByUser()
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function sentByAgent()
    {
        return $this->belongsTo(AiAgent::class, 'sent_by_agent_id');
    }
}
