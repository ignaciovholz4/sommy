<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAgentRun extends Model
{
    protected $table = 'ai_agent_runs';

    protected $fillable = [
        'ai_agent_id', 'conversation_id', 'wa_message_id_in', 'iterations',
        'tool_calls', 'prompt_tokens', 'completion_tokens', 'costo_estimado',
        'status', 'error',
    ];

    protected $casts = [
        'tool_calls' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo(AiAgent::class, 'ai_agent_id');
    }

    public function conversation()
    {
        return $this->belongsTo(WaConversation::class, 'conversation_id');
    }
}
