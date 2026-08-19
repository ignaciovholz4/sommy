<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteChatMensaje extends Model
{
    protected $table = 'reportes_chat_mensajes';

    public $timestamps = false;

    protected $fillable = ['sesion_id', 'role', 'content', 'tool_calls', 'tool_name', 'tool_call_id', 'created_at'];

    protected $casts = [
        'tool_calls' => 'array',
        'created_at' => 'datetime',
    ];

    public function sesion()
    {
        return $this->belongsTo(ReporteChatSesion::class, 'sesion_id');
    }
}
