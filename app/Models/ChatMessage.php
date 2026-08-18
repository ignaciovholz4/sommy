<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    protected $fillable = [
        'user_id',
        'message',
        'response',
        'is_bot',
        'session_id',
        'documentation_id',
        'rating',
        'feedback'
    ];

    protected $casts = [
        'is_bot' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function documentation()
    {
        return $this->belongsTo(Documentation::class);
    }
} 